<?php

namespace App\Providers;

use App\Contracts\ChatModel;
use App\Services\Chat\DemoChatModel;
use App\Services\Chat\GeminiChatModel;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Tanpa API key, chatbot tetap bisa didemokan dengan jawaban yang sudah disiapkan.
        $this->app->bind(ChatModel::class, function () {
            $apiKey = config('chatbot.api_key');

            return filled($apiKey)
                ? new GeminiChatModel($apiKey, config('chatbot.model'), config('chatbot.max_tokens'))
                : new DemoChatModel;
        });
    }

    public function boot(): void
    {
        RateLimiter::for('article-submission', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('chat', fn (Request $request) => [
            Limit::perMinute(config('chatbot.limit_per_minute'))->by($request->ip()),
            Limit::perDay(config('chatbot.limit_per_day'))->by($request->ip()),
        ]);
    }
}
