# Admin video — Flow, coverage, and preparation

## Recording order

Read the three scripts in order:

1. [Dashboard and publishing](03-admin-dashboard-and-publishing.md) — sections 1–9.
2. [Community and engagement](04-admin-community-and-engagement.md) — sections 10–21.
3. [System and closing](05-admin-system-and-closing.md) — sections 22–27, with four optional additional pages.

Aim for approximately **13–17 minutes**, including short navigation pauses. Allow another 2–3 minutes if showing the optional pages. The narration is intentionally shorter than a full tutorial; demonstrate one representative create/detail/edit flow rather than typing every field on camera.

The workflow is: **Dashboard → Categories → Media → Tags → Content → Characters → Events → Merchandise → category recovery → community moderation → users and roles → communication → chatbot → analytics → account and system tools.**

Categories come before category-associated media and content. Media and tags are ready before the content form. Content is ready before character associations and merchandise relationships. Subscribers come before newsletters, and maintained FAQs come before reviewing chatbot usage.

## Sidebar coverage checklist

All 27 active sidebar entries have a spoken section. This table follows sidebar order so it can be checked against the application; script numbers follow recording order.

| Sidebar entry | Section | Pages or controls to show |
| --- | --- | --- |
| Dashboard | 1 | Overview, queues, charts, quick actions |
| Profile | 22 | Profile and password forms |
| Content | 5 | List, filters, create, detail, edit, media and publishing |
| Submissions | 10 | Status list, detail, approve/reject |
| Categories | 2 | List, create, detail, edit |
| Recycle Bin | 9 | Removed categories, restore, permanent-delete control |
| Tags | 4 | List, create, edit |
| Media | 3 | Upload, list, filters, pagination, edit, preview/download |
| Users | 14 | List, detail, administrator creation form |
| Roles | 15 | List, create/edit role definitions |
| Contacts | 16 | List, detail, email reply link |
| Subscribers | 17 | List, detail, edit, status, bulk/export controls |
| Newsletters | 18 | List, create/edit, detail, preview, send control |
| Reviews | 11 | List, detail, approval/rejection/removal controls |
| Ratings | 12 | List, filters, removal control |
| Feedback | 13 | List, detail, status control |
| Characters | 6 | List, create, detail, edit, content associations |
| Events | 7 | List, create, detail, edit, cover/gallery and location |
| Merchandise | 8 | List, create, detail, edit, linked records |
| Chatbot | 20 | Queries, responses, filters, export |
| Chatbot FAQs | 19 | List, create, detail, edit |
| Analytics | 21 | Trends, distributions, popular items, community metrics |
| Activity Logs | 23 | List, filters, detail, export |
| Sessions | 24 | Session list and revoke control |
| Maintenance | 25 | Status, message, bypass routes |
| Logs | 26 | Viewer, download, clear control |
| Backups | 27 | Creation control, files, download/delete controls |

Settings, Notifications, and IP Restrictions have routes and views, but their sidebar links are commented out. Health has a separate route and view without an active sidebar entry. Optional sections A–D cover these pages without presenting them as visible sidebar features. On the live deployment, append their paths to the application's existing base URL, including its `/public` prefix.

## Prepare a connected example

- Use the administrator credentials documented in the main project README.
- Choose one demonstration fandom and reuse it through the media, content, character, event, and merchandise examples. This makes relationships easy to follow.
- Prepare a short article, matching images, one character, an event, and one merchandise item. Use existing complete examples where possible and prefill longer text before recording.
- Prepare a pending member submission and review to demonstrate the moderation lifecycle. Approve only demonstration records you intend to publish.
- Prepare a sample contact, feedback entry, subscriber, draft newsletter, and FAQ so detail pages are populated.
- Open both the admin record and its public page in separate tabs for one clear before-and-after example.
- Use a disposable category for a recycle-bin demonstration. The category bin is not a recovery system for every module.
- Show destructive controls without activating them. Keep maintenance, IP access, sessions, role assignments, and existing data unchanged during the walkthrough unless deliberately testing locally.
- Preview a newsletter draft rather than sending to real subscribers. Mail delivery and submission-decision emails depend on configured mail services.
- Keep user addresses, session/IP details, log contents, secrets, and backup contents out of the final video. Use demonstration data or frame only the relevant controls.

## Keep the narration accurate

- The current user-creation form creates an administrator account; do not demonstrate it as a generic member-registration form.
- Roles currently exposes role definitions. Do not promise a granular permission editor based only on the existence of backend permission code.
- There is no separate active sidebar module for upcoming releases: demonstrate the relevant release fields in Content and Merchandise.
- Merchandise is currently a catalogue, not an order, inventory, payment, or checkout dashboard.
- Contact replies use a `mailto:` link; this is not an internal email conversation interface.
- Backups exports database SQL. Do not describe it as a full-site/media backup or an automated restore tool.
- Analytics reflects the application's recorded data. Do not call it real-time visitor tracking, revenue reporting, or an external analytics integration.
- Check the deployment before recording; these scripts were checked against local routes, views, and supporting controller logic, not a live execution of every administrative action.
- The design explanations are suggested presenter wording; adapt them to your own design intentions.

End on the dashboard and one public result. This shows evaluators how administration connects to the visitor experience, rather than ending with an unrelated system form.
