# Product Requirements Document (PRD)

FAN HUB PLUS | ADMIN PANEL PRD

FAN HUB PLUS

Admin Panel

CONTENT COMMUNITY ANALYTICS

Purpose: Define exactly what the Fan Hub Plus administrator can see, manage, moderate and measure, and how each admin screen maps to the approved SRS and final database schema.

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 1

### Project

### Fan Hub Plus \- Fandom Universe Portal

### Document

### Admin Panel Product Requirements Document

### Version

### 1.0

### Prepared from

### Fan Hub Plus SRS v1.0 + Final Database Schema

### Date

### 23 September 2026

### Project-ready specification for Laravel implementation

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 2

0. Document Control and Source Basis

### Item

### Decision

### Primary source

### Fan Hub Plus Software Requirements Specification, Version 1.0

### Database source

### Fan Hub Plus Final Database Schema

Actual application roles Registered User and Admin Guest / Visitor

### Unauthenticated visitor; not a database role

### Merchandise scope

### Display / discovery only; no cart, checkout, order or payment

### Chatbot

> Optional feature; admin FAQ and query screens may be
> deferred

### Admin control scope

Content, media, users, moderation, feedback, optional chatbot knowledge base and usage statistics

> Important: This PRD separates requirements supported by the SRS/database from implementation
> recommendations. Recommended additions are clearly labelled and are not treated as existing database fields.

## Contents Section

### Topic

### 1-4

### Admin purpose, access model, navigation and global UI rules

### 5-9

> Admin Dashboard: layout, KPIs, charts, widgets and quick
> actions

### 10-22

### Detailed admin modules and workflows

### 23-30

Analytics, routes, database mapping, security and non-functional requirements

### 31-35

Errors, test criteria, implementation order, schema gaps and final screen inventory

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 3

## 1. Admin Panel Purpose

The admin panel is the operational control center for Fan Hub Plus. It must let an authorized Admin manage the content ecosystem, moderate community input, keep the media library organized, manage users and view usage statistics without exposing administrative actions to normal registered users.

## Primary admin outcomes

##  Publish accurate fandom content across Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga and Cosplay.  Moderate fan-submitted content and written reviews before they become public.  Manage multimedia assets, character profiles, merchandise showcases and location-aware events.  Respond to feedback using a clear status workflow.  Measure platform usage using content views, popularity, category performance and other available activity data.  Maintain optional chatbot FAQs and inspect chatbot interaction history if the chatbot feature is implemented. Admin is not an e-commerce operator

Out of scope: The admin panel must not include orders, payments, checkout, carts, refunds, inventory purchasing or customer payment management. Merchandise is showcase-only in both the SRS and final schema.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 4

2. Admin Access and Authorization Model

```text
Actor Authentication Admin access Notes
Guest / Visitor No No
```

```text
Browses public application only;
not stored as a role.
```

Registered User Yes No bookmarks, ratings, reviews and

Uses user dashboard, favorites, submissions.

### Admin Yes Yes

> Accesses /admin area and
> management actions.

## Authorization flow

1. User signs in through normal Laravel authentication.
2. Application checks the user role through role_user \-> roles.
3. If role slug is admin, redirect or allow access to /admin/dashboard.

4. If role is registered-user, deny admin routes and return the normal user dashboard or a 403 response.

5. Every admin route must be protected server-side; hiding sidebar links alone is not sufficient.

## Implementation recommendation

Laravel: Use an Admin middleware or authorization Gate/Policy. Keep admin authorization centralized so every CRUD action is checked consistently.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 5

3. Admin Information Architecture

### Navigation Group

### Screens

### Overview

### Dashboard

### Content

### Categories; Content; Media Library; Tags; Character Profiles

### Discovery

### Merchandise; Events

### Community

### User Submissions; Reviews; Ratings; Feedback

### Users

### All Users

### AI \- optional

### FAQ Knowledge Base; Chat History

### Reports

### Analytics

### Account

### Admin Profile; Logout

## Recommended sidebar behavior  Collapsible navigation groups on desktop; drawer navigation on tablet/mobile.  Show numeric badges only where action is required, for example Pending Submissions, Pending Reviews and Open Feedback.  Persist the active navigation state and highlight the current page.  Do not display optional Chatbot navigation when the feature is disabled.  Keep Dashboard and Content near the top because they are the most frequent admin destinations.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 6

4. Global Admin UI and Interaction Rules

### Area

### Requirement

### Page header

> Title, short context line, optional primary action button and
> breadcrumbs.

### Data tables

Search, filters, sortable columns where useful, pagination, empty state and row actions.

### Forms

```text
Clear labels, required markers, inline validation, save/cancel actions
and unsaved-change warning.
```

### Statuses

Use consistent status chips for Draft, Pending, Published, Rejected, Open, Resolved, etc.

### Destructive actions

Require confirmation and explain related-record constraints.

### Accessibility

Legible fonts, keyboard-friendly controls, alt text support and visible focus states.

### Responsive behavior

Desktop-first admin experience that remains usable on tablets and smaller screens.

### Loading/error states

Use spinners/skeletons for heavy media pages and actionable error messages.

SRS alignment: The SRS requires user-friendliness, accessibility, performance, compatibility, reliability and security. These rules should apply to every admin page, not only the public site.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 7

5. Admin Dashboard \- Screen Specification

## The Dashboard is a read-mostly overview page. It should answer four questions immediately: What requires admin attention? What is being used most? What is coming up? What can I create quickly? Recommended desktop layout

```text
Welcome + date range / refresh Quick actions
```

Total Users Total Content Pending Submissions Upcoming Events

> Content by Status
> Popular Content

> Popular Categories
> Popular Categories

### Recent Submissions

> Upcoming Events
> Upcoming Events

### Recent Feedback

### System notes / optional chatbot volume

## Dashboard interaction rules  All KPI cards should be clickable and open the corresponding filtered management page.  Dashboard must never be the only place to access a management action.  Default date range for trend charts: last 30 days. Provide 7 days, 30 days, 90 days and All Time options where data supports it.  The page should load useful totals even if optional analytics data is unavailable.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 8

## 6. Dashboard KPI Cards

```text
KPI Primary data source Definition / calculation Click-through
Total Users users Count all registered database
```

### users. Users

Total Content contents Count all content records. Content

Published Content contents status = published. Content filtered to Published Pending Submissions contents

> is_user_submitted = true AND
> status = pending_review. User Submissions

```text
Total Categories categories Count fandom categories. Categories
Upcoming Events events start_at >= now and status =
```

### published. Events filtered to upcoming

```text
Pending Reviews reviews status = pending. Reviews
Open Feedback feedback status = open. Feedback
Total Media media Count central media records. Media Library
Chatbot Queries - optional chatbot_queries Count queries for selected period. Chat History
```

Do not fake metrics: The SRS mentions active users. The current schema does not include a dedicated last_seen field. Only show an Active Users KPI if Laravel session/activity data can reliably support it; otherwise omit it or add explicit activity tracking.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 9

7. Dashboard Charts and Analytics Blocks

```text
Chart / Block Recommended visualization Data logic
Content by Status Donut / stacked bar
```

> Group contents by status: draft,
> pending_review, published, rejected.

### Content by Type Bar / donut

> Group contents by article, video, audio,
> image.

### Popular Categories Horizontal bar

```text
Aggregate content view_count by category;
optionally combine with popularity_score.
```

### Top Content Ranked table

Order published contents by view_count or popularity_score.

```text
User Growth Line chart Group users.created_at by day/week/month.
```

### Feedback Mix Donut

Group feedback by bug, suggestion, query and status.

### Review Moderation Small bar

> Count pending, approved and rejected
> reviews.

Chatbot Volume \- optional Line chart Group chatbot_queries by date.

## Popularity calculation note

The database has both contents.view_count and contents.popularity_score. The project should define a single rule for what the admin chart means by "popular". The simplest transparent rule is to sort by view_count. If popularity_score is calculated by a custom formula, document that formula in code and project documentation.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 10

8. Dashboard Attention Widgets

```text
Widget Rows to show Columns / information Primary action
Recent User Submissions 5-8
```

> Submitter, title, category,
> submitted date, status Review

```text
Recent Feedback 5-8 Type, short message,
```

### user/guest, date, status Open feedback

Upcoming Events 5 Title, city, start date, status View / edit event

Popular Content 5 Title, category, type, views View content Pending Reviews 5 User, resource, excerpt,

### submitted date Moderate

## Empty-state examples  No pending submissions: "All caught up \- no fan submissions are waiting for review."  No upcoming events: provide an "Add Event" action.  No feedback: show a neutral empty state rather than an empty table.  No media: show an "Upload Media" call-to-action.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 11

9. Dashboard Quick Actions and Filters

## Quick actions Action

### Destination

### Add Content

### Content create form

### Upload Media

### Media upload screen / modal

### Add Event

### Event create form

### Add Character

### Character create form

### Add Merchandise

### Merchandise create form

Review Submissions Pending User Submissions queue

## Dashboard filters  Date range for trend charts where timestamp data supports it.  Optional category filter for content/category analytics.  Refresh button to reload dashboard data without changing stored records.  Do not apply a date filter to lifetime totals unless the card label explicitly shows the selected period.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 12

## 10. Users Management

```text
Screen Purpose Key data / actions
All Users Browse registered accounts
```

```text
Name, email, verification, role, joined date;
view/edit.
```

### User Detail Inspect a single user

Profile, favorite categories, submissions, bookmarks, ratings, reviews, feedback.

### Edit User Maintain supported user fields

> Name, email; role assignment through
> role_user.

## Filters and search  Search by name or email.  Filter by role: Registered User / Admin.  Filter by email verification status if required by the UI.  Sort by newest/oldest joined date.

Schema limit: The current users table does not include active, blocked, suspended or deleted status fields. Do not build a Ban/Suspend feature unless the schema is extended deliberately.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 13

## 11. Categories Management

```text
Field Type / source Validation / behavior
Name
```

categories.name Required; human-readable fandom name.

Slug categories.slug Required, unique, URL-safe. Description categories.description Optional long description. Icon

categories.icon_media_id Optional selection from Media Library.

## Admin actions  Add category.  Edit category.  View related content/characters/merchandise/events.  Delete only when referential integrity rules are satisfied or a safe reassignment process exists.

Seed categories: Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga and Cosplay should be available as the initial categories defined by the project scope.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 14

## 12. Media Library

Capability Upload Browse Filter Metadata Usage Delete

Requirement Images, video, audio and documents; store path/URL and metadata, not file binary.

```text
Grid/list view with preview, filename, type, size and upload date.
media_type: image, video, audio, document.
Alt text; dimensions for images; duration for video/audio where
available.
```

Allow media selection from category, content, character, merchandise and event forms. Prevent or warn when a media record is still referenced by another entity.

## Recommended upload validation  Whitelist MIME types.  Set reasonable file-size limits.  Generate safe unique storage names while keeping original_filename for display.  Require meaningful alt text for important public images where possible.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 15

## 13. Content Management

### List column

### Source / behavior

### Title

### contents.title

### Category

### categories through category_id

### Type

### article / video / audio / image

### Status

### draft / pending_review / published / rejected

### Featured

### contents.is_featured

### Views

### contents.view_count

### Release date

### contents.release_date

### Actions

```text
View, edit, publish/reject as allowed, feature/unfeature, delete with
confirmation
```

## Filters  Category  Content type  Status  Featured only  Release year/date  Tag  User-submitted only Search and sorting  Search title, excerpt and optionally body.  Sort by latest, popularity/views or alphabetically, matching the SRS discovery requirements.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 16

## 14. Content Create / Edit Form

### Section

### Fields

### Basic

### Title, slug, category, type, release date

### Editorial

### Excerpt, body

### Publishing

### Status, is_featured, published_at

### Discovery

```text
Tags; popularity score is system/admin-defined rather than free-form
if possible
```

### Media

### Cover, gallery, trailer, audio clip, attachment through content_media

### Submission metadata

```text
is_user_submitted, submitted_by, reviewed_by - normally system-
controlled
```

## Content-media roles Role

*Use*

### cover

### Primary thumbnail / hero image

### gallery

### Additional images

### trailer

### Video trailer / embedded media reference

### audio_clip

### Podcast, soundtrack or related audio

### attachment

### Other supported file/resource

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 17

15. User Submission Moderation

### Queue field

### Display

### Submitter

### User name / email from submitted_by

### Content

### Title, type, category, short excerpt

### Submitted at

### contents.created_at

### Status

### Usually pending_review in the moderation queue

### Actions

### Preview, edit if policy allows, approve/publish, reject

## Approval workflow

6. User submits content; record is marked is_user_submitted = true and status = pending_review.

7. Admin opens the item and reviews text, media, category and tags.

8. If accepted, set status = published, reviewed_by = admin id and published_at = current time.
9. If rejected, set status = rejected and reviewed_by = admin id.
10. Published content appears in normal public discovery according to application rules.

Optional enhancement: The schema has no rejection_reason column. If you want the user to see why a submission was rejected, add a moderation note/rejection reason field rather than hiding the reason in unrelated data.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 18

## 16. Tags Management

### Screen

### Behavior

### Tag List

Name, slug, usage count, edit/delete action.

### Create/Edit Tag

Required name; unique slug.

### Content Assignment

Attach multiple tags to a content item through content_tags.

Purpose: Tags support advanced discovery and filtering beyond the eight top-level fandom categories. Keep tags reusable and avoid creating duplicates with minor spelling differences.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 19

## 17. Character Profiles

```text
Field Source Behavior
Name character_profiles.name Required.
Slug character_profiles.slug Unique.
Category category_id Required fandom category.
Bio bio Optional profile text.
Image image_media_id Optional media selection.
```

### Related content character_contents

```text
Attach articles/videos/audio/images to the
character.
```

## Character detail admin view  Profile preview.  Related content list.  Add/remove related content.  Edit profile.  Delete with confirmation and relationship cleanup.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 20

18. Merchandise Showcase Management

Field Category Name + slug Description Image Tag Upcoming Views

```text
Allowed values / behavior
Optional/required according to form policy; schema uses
category_id.
```

> Display name and unique URL slug.
> Display-only merchandise description.
> Single primary image through image_media_id.
> limited_edition / pre_order / collectible / standard
> is_upcoming true/false
> view_count; read-only analytics field

Scope lock: Do not add stock quantity, price checkout logic, orders, payment gateway controls or fulfillment

screens. The project explicitly excludes actual purchasing.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 21

## 19. Events Management

### Form group

### Fields

### Basic

### Title, description, optional category

### Location

### City, venue, address, latitude, longitude

### Schedule

### Start date/time, optional end date/time

### External link

### Ticket URL

### Visual

### Cover media

### Publishing

### draft / published / cancelled

## Validation  start_at is required.  end_at, when present, must not be before start_at.  Latitude/longitude should be validated as coordinates.  Ticket URL must be a valid URL when provided.  Cancelled events should remain visible to admins even if hidden or labelled on the public site.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 22

## 20. Reviews and Ratings

## Written reviews Status

### Admin behavior

### pending

Needs moderation; approve or reject.

### approved

Can appear publicly.

### rejected

Retained for admin record unless deletion policy says otherwise.

## Ratings  Ratings support either 1-5 stars or thumbs up/down.  Admin normally views aggregated or individual ratings but does not need a heavy moderation workflow unless abuse handling is added.  Validate stars only when rating_type = star, and thumbs value only when rating_type = thumbs.

Schema behavior: Reviews and ratings are polymorphic. The admin UI should show a friendly target label such as Content: "Top Anime Openings" rather than raw reviewable_type/rateable_type class names.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 23

## 21. Feedback Management

Type bug suggestion query Status flow open in_review resolved closed

Meaning User reports a defect or broken experience. User proposes an improvement. User asks a question.

Meaning New or not yet handled. Admin is investigating. Issue/query has been handled. Finalized and archived from active attention.

## Feedback list filters  Type  Status  Date range  Registered user vs guest where user_id is null

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 24

22. Chatbot Administration \- Optional

Optional feature: The SRS and final schema treat the chatbot as optional. This module can be hidden entirely until chatbot functionality is implemented.

## FAQ Knowledge Base Field

### Source

### Category

### chatbot_faqs.category_id \- optional

### Question

### chatbot_faqs.question

### Answer

### chatbot_faqs.answer

### Created by

### chatbot_faqs.created_by

## Chat History  Show user or Guest, session ID, message, response and created date.  Provide search by message text and filter by date.  Use query volume for the optional dashboard analytics card/chart.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 25

## 23. Analytics and Reports

### Report Can be produced from current schema? Logic

Top content by views Yes contents.view_count DESC Top merchandise by views Yes

### merchandise_items.view_count DESC

### Content by category Yes

### Count contents grouped by category

Published vs draft content Yes Group contents by status

User growth Yes Group users by created_at Review moderation volume Yes Group reviews by status Feedback volume Yes Group feedback by type/status Chatbot interaction volume Optional

### Count/group chatbot_queries

True active users Not reliably guaranteed Needs dependable session or activity tracking

## Export recommendation

CSV export for filtered admin tables can be useful, but it is not required by the supplied SRS. Treat exports as an enhancement after the mandatory functionality is complete.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 26

24. Admin Profile and Preferences

Setting Name / email Display name Avatar Bio Theme Font size Password

Source / behavior users user_profiles.display_name user_profiles.avatar_media_id user_profiles.bio light / dark / system small / medium / large Use Laravel password update flow; password hash only in database

Accessibility: Theme and font-size preferences already exist in user_profiles, so the admin layout can reuse

the same preference system.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 27

25. Search, Filter, Sort and Pagination Standards

### Component

### Standard behavior

### Search

```text
Debounced or submit-based search; preserve query in URL where
practical.
```

### Filters

Show active filter chips and provide Clear All.

### Sorting

Default to a logical stable sort, usually newest first for operational queues.

### Pagination

Server-side pagination for large tables; preserve filters across pages.

### Bulk actions

```text
Only add where they clearly reduce work; avoid risky bulk delete by
default.
```

### Empty state

Explain why no records appear and offer the next useful action.

## Recommended list page defaults  25 rows per page.  Search at top left; primary Add button top right.  Filters in a compact row above the table.  Row actions in a menu to avoid wide tables.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 28

26. Status, Delete and Data Integrity Rules

### Area

### Rule

### Content

```text
Use status changes for draft/publish/reject; delete only with explicit
confirmation.
```

### User submissions

```text
Moderate through pending_review -> published/rejected.
```

### Reviews

```text
Moderate through pending -> approved/rejected.
```

### Feedback

```text
Progress through open -> in_review -> resolved -> closed.
```

### Events

```text
draft / published / cancelled.
```

### Categories

> Do not delete if related records would break referential integrity
> unless reassignment/cascade is intentionally designed.

### Media

Warn/prevent deletion when referenced.

### Tags

Detach safely from content_tags before removal.

Recommendation: Prefer reversible status changes over destructive deletion for operational content. The current schema does not define soft-delete columns, so implement soft deletes only if you intentionally add them.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 29

27. Security and Authorization Requirements

 Require authenticated admin access for every /admin route and action.
 Use CSRF protection on state-changing forms.
 Validate and sanitize all submitted data on the server.
 Hash passwords using Laravel authentication conventions; never display or log plain passwords.
 Validate uploaded files by MIME type and size; store them outside unsafe executable paths.
 Use authorization checks for view, create, update, publish, reject and delete operations.
 Escape user-generated text when rendering to prevent script injection.
 Protect external URLs and embedded media inputs with validation.

 Do not expose internal storage paths, raw class names or sensitive database details to normal users.

SRS requirement: Security is a minimum non-functional requirement, including authentication and protection

of personalized features.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 30

28. Admin Non-Functional Requirements

### Quality

### Admin expectation

### Performance

```text
Dashboard and list pages should load quickly; paginate large data;
optimize media previews.
```

### Reliability

Actions should provide clear success/failure feedback and avoid partial updates.

### Scalability

Queries and relationships should support growth in users, content and media.

### Availability

Admin should be available whenever the application is operational.

### Compatibility

Support current major browsers and responsive layouts.

### Accessibility

Readable typography, clear controls, alt text, keyboard accessibility.

### Usability

> Consistent layout and predictable navigation; no hidden critical
> actions.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 31

29. Recommended Laravel Admin Routes

Implementation recommendation: The following routes are a clean implementation pattern, not a mandatory

naming scheme from the SRS.

Area Dashboard Users Categories Content Submissions Media Tags Characters Merchandise Events Reviews Ratings Feedback Chatbot FAQ \- optional /admin/chatbot/faqs Chat history \- optional /admin/chatbot/queries Analytics Profile

Example route /admin/dashboard /admin/users /admin/categories /admin/contents /admin/submissions /admin/media /admin/tags /admin/characters /admin/merchandise /admin/events /admin/reviews /admin/ratings /admin/feedback

```text
/admin/analytics
/admin/profile
```

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 32

30. Admin Screen to Database Mapping

### Admin module

### Main tables

Authentication / Users users, roles, role_user, user_profiles Categories

### categories, media

### Content

### contents, content_media, media, tags, content_tags

### Submissions

### contents, users

### Characters

### character_profiles, character_contents, contents, media

### Merchandise

### merchandise_items, categories, media

### Events

### events, categories, media

```text
Bookmarks analytics bookmarks
Ratings
```

### ratings

### Reviews

### reviews

### Feedback

### feedback

### Chatbot \- optional

> chatbot_faqs, chatbot_queries
> Dashboard / Analytics Aggregates from the tables above

```text
Polymorphic data: bookmarks, ratings and reviews use type + id pairs. Laravel model validation/policies must
protect integrity because a single normal database foreign key cannot reference multiple target tables.
```

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 33

31. Admin Error States and Edge Cases

### Scenario

### Expected behavior

### Duplicate slug

> Block save and show the field that must be changed.

```text
Delete category with related data Block or require explicit reassignment; never silently orphan records.
Delete referenced media Warn/prevent until references are removed or replaced.
Expired/deleted related polymorphic target Show a safe "Unavailable resource" label rather than crashing.
Invalid event dates
```

> Reject form submission; end cannot precede start.

### Invalid rating combination

> Reject stars outside 1-5 or missing thumbs value.

Admin loses role during session Next protected request must deny admin access. Large upload

> Reject with clear size/type message.

### No records

> Show purposeful empty state and create action.

### Server error

> Preserve user-entered form data where possible and show a retry-safe
> message.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 34

32. Acceptance Criteria and Test Checklist

### Area

### Acceptance criteria

### Authorization

> Registered users cannot access admin routes; Admin can.

### Dashboard

> Loads KPI totals and actionable queues without broken links.

### Content

> Admin can create, edit, draft, publish and manage media/tags.

### Submissions

> Admin can review pending user content and publish/reject it.

### Categories

> Admin can manage categories without breaking related data.

### Media

> Uploads create media records and reusable selections.

### Characters

> Related content can be attached/detached.

### Merchandise

> Display items can be managed without any purchase flow.

### Events

> Admin can manage date/location/status and ticket URL.

### Reviews

> Pending written reviews can be approved/rejected.

### Feedback

> Status can move through the defined workflow.

### Analytics

> Reports use transparent calculations from available data.

### Responsive UI

> Core admin tasks remain usable on common screen sizes.

### Security

> Validation, CSRF protection and role checks exist server-side.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 35

33. Recommended Implementation Order
11. Admin authentication and authorization middleware.
12. Admin layout: sidebar, top bar, breadcrumbs, alerts and reusable table/form components.
13. Dashboard shell with basic totals.
14. Categories and Media Library.
15. Content CRUD, tags and content media.
16. User submission moderation.
17. Character profiles and character-content relationships.
18. Merchandise showcase.
19. Events and location fields.
20. Reviews, ratings and feedback.
21. Users management and profile pages.
22. Analytics refinements and charts.
23. Optional chatbot FAQ/history module last.

Why this order: It completes the mandatory content-management core first and prevents optional chatbot work from delaying the required project functionality.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 36

34. Schema Gaps and Optional Enhancements

The current schema is strong enough for the required admin panel. The following are optional additions only if the product needs these behaviors:

Need Current support Optional addition

User suspension / blocking Not present users.status or suspended_at Soft delete / restore Not present deleted_at on selected tables Rejection reason for submissions Not present rejection_reason / moderation_note Precise active-user analytics Not guaranteed

> last_seen_at or user_activity table, or
> dependable session analytics

Audit trail of admin changes Not present admin_audit_logs table

Multiple merchandise gallery images Only one image_media_id merchandise_media pivot if needed Event tags / multiple categories Not present

> event_tags or event_categories pivot if product
> requires it

Do not overbuild: Add these only when they solve a real project requirement. The supplied SRS does not require most of them, so mandatory features should come first.

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 37

35. Final Admin Screen Inventory

```text
# Screen Priority
1 Admin Login / protected entry Must
2 Dashboard Must
3 Users List Must
4 User Detail / Edit Must
5 Categories List + Create/Edit Must
6 Media Library + Upload Must
7 Content List Must
8 Content Create/Edit Must
9 User Submissions Queue Must
10 Submission Review Must
11 Tags Must for advanced discovery
12 Character Profiles Must
13 Merchandise Must
14 Events Must
15 Reviews Moderation Must
16 Ratings View Useful
17 Feedback Must
18 Analytics Must at basic level
19 Admin Profile Must
20 Chatbot FAQ Optional
21 Chat History Optional
```

## Definition of done

```text
The admin panel is complete when an authorized Admin can manage the mandatory content and community workflows
end-to-end, the dashboard accurately summarizes available data, all destructive/status-changing actions are validated,
and normal registered users cannot access admin functionality.
```

```text
Final scope note: Fan Hub Plus is an information and fandom discovery platform. Keep the admin panel
focused on publishing, curation, moderation, discovery resources, events and analytics - not e-commerce.
```

---

FAN HUB PLUS | ADMIN PANEL PRD

Fan Hub Plus \- Admin Panel Product Requirements Document |Page 38

A. Source Reference

 Fan Hub Plus \- Software Requirements Specification, Version 1.0, Theme: Fandom Universe, Category: End-to-End Web
Solutions.

 Fan Hub Plus \- Final Database Schema, including users/roles, profiles, media, categories, contents, tags, characters,
merchandise, events, bookmarks, ratings, reviews, chatbot and feedback tables.

This PRD converts those approved project requirements and tables into an implementable Admin Panel specification. Items explicitly marked as recommendations or optional enhancements are implementation guidance, not existing SRS/database requirements.