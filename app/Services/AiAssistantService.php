<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Messages\MessageParam;
use App\Models\Project;

class AiAssistantService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(apiKey: config('services.anthropic.key'));
    }

    public function suggestTasks(Project $project, array $existingTasks): array
    {
        $taskList = implode(', ', array_slice(array_column($existingTasks, 'title'), 0, 20));
        $context  = $taskList ? "Existing tasks: {$taskList}." : 'No tasks yet.';

        $prompt = "Project: \"{$project->name}\"\n"
            . ($project->description ? "Description: {$project->description}\n" : '')
            . $context
            . "\n\nSuggest 5 concrete, actionable tasks for this project that don't duplicate existing tasks. "
            . "Respond ONLY with a JSON array like: [{\"title\":\"...\",\"priority\":\"low|medium|high\",\"description\":\"one sentence\"}]";

        $message = $this->client->messages->create(
            maxTokens: 800,
            messages: [['role' => 'user', 'content' => $prompt]],
            model: 'claude-haiku-4-5',
        );

        $text = $message->content[0]->text ?? '[]';

        if (preg_match('/\[.*\]/s', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return array_map(fn($t) => [
                    'title'       => (string) ($t['title'] ?? ''),
                    'priority'    => in_array($t['priority'] ?? '', ['low', 'medium', 'high']) ? $t['priority'] : 'medium',
                    'description' => (string) ($t['description'] ?? ''),
                ], $decoded);
            }
        }

        return [];
    }

    public function chat(string $userMessage, array $history, ?Project $project = null): string
    {
        $systemPrompt = 'You are DevFlow AI, a helpful project management assistant. '
            . 'Keep answers concise and practical.';

        if ($project) {
            $systemPrompt .= " The user is working on the project \"{$project->name}\""
                . ($project->description ? ": {$project->description}" : '') . '.';
        }

        $messages   = array_map(fn($m) => ['role' => $m['role'], 'content' => $m['content']], $history);
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $response = $this->client->messages->create(
            maxTokens: 600,
            messages: $messages,
            model: 'claude-haiku-4-5',
            system: $systemPrompt,
        );

        return $response->content[0]->text ?? 'Sorry, I could not generate a response.';
    }
}
