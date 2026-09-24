# Public chatbot

The public layout includes the floating assistant. It supports visitors and signed-in users, light/dark themes, FAQ suggestions and Gemini answers. FAQ questions are read from `chatbot_faqs`; normalized exact matches are answered locally without requiring an API key. Other questions use Gemini with up to 40 FAQ entries and six previous successful turns from the same session and user. Successful conversations are stored in `chatbot_queries`.

## Setup

1. Run `php artisan migrate` if the existing chatbot migrations have not been applied.
2. Set `GEMINI_API_KEY` in your local `.env`. Never use a `VITE_` prefix for this secret.
3. Set `GEMINI_MODEL` to a generateContent-compatible model available to your Google project (default: `gemini-3.5-flash`).
4. Run `php artisan config:clear` and `npm run build`.
5. Populate `chatbot_faqs` with your approved questions and answers using the existing `ChatbotFaq` model. The first six records appear as suggestions. No invented site policies are seeded.

API reference: https://ai.google.dev/api

The POST endpoint uses web-session CSRF protection and a 12 requests/minute throttle. The API key is sent only from Laravel to Google. AI questions and recent conversation context are shared with Google; the widget discloses this and local conversation storage. Configure retention for saved conversations according to your site's needs. The widget does not reload stored history into the browser after navigation.

Run `php artisan test --filter=ChatbotTest` for isolated SQLite tests with fake Gemini responses. A live Gemini response requires a valid key and model access.
