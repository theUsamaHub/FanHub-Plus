# Fandom Universe

![Page 1 image](images/page-001-image-01.png)

## Version 1.0

Portal for Fans

![Page 1 image](images/page-001-image-02.png)

Software Requirements Specification

![Page 1 image](images/page-001-image-03.png)

© Aptech Limited

### Theme: Fandom Universe

![Page 1 image](images/page-001-image-04.png)

```text
Category: End-to-End Web Solutions
Project Name: Fan Hub Plus
```

---

© Aptech Limited

## Table of Contents

1.1 Background and Necessity for the Web Application..... 3
1.2 Proposed Solution..................................................................... 4
1.3 Purpose of the Document...................................................... 5
1.4 Scope of Project........................................................................ 6
1.5 Constraints.................................................................................. 7
1.6 Functional Requirements........................................................ 7
1.7 Non-Functional Requirements............................................. 11
1.8 Interface Requirements......................................................... 13
1.9 Project Deliverables................................................................ 15

---

© Aptech Limited

# 1.1 Background and Necessity for the Web Application

Fandom is a group or community of fans sharing a common passion. Fandom enthusiasts exist across Anime, Gaming, Movies, TV Shows, Korean Pop (K-Pop), Comics, Manga, and Cosplay communities.

Today they rely on a scattered mix of forums, social media pages, and niche sites to find the content they love. Existing resources are often single-fandom, cluttered with ads, difficult to navigate, or lack the rich media experience that fans genuinely want.

![Page 3 image](images/page-003-image-01.png)

There is a growing demand for a single, engaging, and visually rich information hub that brings multiple fandoms together under one roof. A Web application is required to fulfil this demand by offering a dynamic and responsive platform which provides features such as:

•Curated and categorized content spanning multiple fandom domains

•Search, sorting, and filtering across all content types •Rich multimedia experiences including galleries, videos, audio, and trailers Built with modern front-end technologies and a robust backend, it should ensure a fast, seamless, and immersive browsing experience. This makes it the go-to destination for fandom communities to explore, discover, and celebrate the universes they love.

---

© Aptech Limited

# 1.2 Proposed Solution

![Page 4 image](images/page-004-image-01.png)

To address the gap in a unified, engaging fandom experience, it is proposed to develop a fully functional Web application named Fan Hub Plus \- a responsive and interactive Fandom Universe platformdesigned for anime watchers, gamers, movie and TV buffs, K-Pop stans, comic and manga readers, and cosplay enthusiasts alike. The application offers a seamless user experience by organizing content into clearly browsable categories \- Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga, and Cosplay. Each category ispresented through an intuitive UI, interactive galleries, and visual storytelling, optionally with an Artificial Intelligence (AI)-powered chatbot on hand to help visitors find exactly what they are looking for.

---

© Aptech Limited

# Flow Diagrams

Broad level flow diagrams depicting interaction between various entities and the application are shown here.

![Page 5 image](images/page-005-image-01.png)

```text
For example, a visitor's journey from landing page → category browsing →
content detail→chatbot (Optional)→bookmarking, and an admin content-
management flow.
```

# 1.3 Purpose of the Document

This document presents a detailed description of the Fan Hub Plus application, explaining its features, purpose, scope, and limitations. It is intended for both stakeholders and developers of the application.

---

© Aptech Limited

# 1.4 Scope of Project

![Page 6 image](images/page-006-image-01.png)

Fan Hub Plus is an End-to-End Web solution designed to serve as an engaging and visually rich information hub for fandom enthusiasts. It offers a personalized experience through user registration and login, curated content across multiple categories \- Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga, and Cosplay \- and a customizable dashboard. User roles can comprise Visitor, Registered users, and Administrators. Visitors can browse and can have limited access to the Web application. Registered users can browse and explore content with robust search, sorting, and filtering capabilities. They can also discover image galleries, videos, audio clips, featured articles, character profiles, event highlights, trailers, merchandise showcases, and upcoming release listings. An optional AI-powered chatbot can assist visitors by answering frequently asked questions and guiding them through the platform. The application includes a backend system to support data storage, user session management, feedback submission, and content updates. Built with modern technologies, the platform is scalable, responsive, and adaptable for fan communities, fan conventions, and personal use. Administrative functionality will also be part of the application, giving full control over content, media, including user management, the chatbot's knowledge base, and so on.

---

© Aptech Limited

# 1.5 Constraints

Development of the Fan Hub Plus Web application must adhere to several constraints to ensure its successful implementation and operation. Technically, the application must be compatible with major Web browsers and responsive across various devices. You may encounter constraints related to data storage, media file sizes, data synchronization, and backup procedures. The usage of fandom-related images, videos, audio clips, and other media may be subject to licensing agreements and copyright restrictions. It is important to understand and comply with these constraints to avoid legal issues. The Web application will not have any functionality for actual merchandise purchase, order processing, or payment gateways \- merchandise showcases are for display and discovery purposes only, and this functionality is beyond the scope of the application.

# 1.6 Functional Requirements

The Fan Hub Plus Web application will offer a complete and category-based fandom exploration experience with dynamic frontend features and robust backend support. Following are the detailed functional requirements:

User Authentication and Management
•Secure session management
•Forgot password/reset password feature and email verification or tokenized
link

•User profile creation with editable favorite fandoms, categories of interest,
and display preferences
•Optional profile picture/avatar upload feature
Personalized Dashboard

•It displays personalized greeting, recent activity, favorite fandoms, and
bookmarked items

---

© Aptech Limited

Fandom Content Explorer (with Advanced Filters)

•It fetches and displays curated content \- articles, profiles, or media \- across
categories (Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga,
Cosplay) from a backend database

•Support for a multi-level search and filtering feature (category, genre,
release year, popularity, or content type)

•Various Sorting options (latest, most popular, or alphabetical)
AI-Powered Chatbot Assistant (Optional Feature)

You may optionally use AI tools to build a conversational chatbot that has following features: •Answers frequently asked questions about

*the*

![Page 8 image](images/page-008-image-01.png)

platform and its content
•Optionally, recommends content and
categories based on visitor preferences and
conversation context
•Guides new users through the platform's
features via a multi-step conversational flow
•Includes chat history stored for context
continuity and progress tracking
Interactive Multimedia Center

•Enables streaming embedded videos, trailers, audio clips (podcasts or
soundtracks), and animated explainers

•Enables Admin-controlled tagging and categorization of media
•Supports User feedback/rating on media (5-star or thumbs-up/down
system)
Character Profiles and Featured Articles Hub

•A Card-based character profiles with fandom-based and category-based
filtering

•The Featured articles with rich text, embedded images, and timeline-style
event highlights

•An Event highlights section (conventions, premieres, releases) presented in
a storytelling format

---

© Aptech Limited

•An option for users to submit fan content or articles (admin approval
required)

### Merchandise Showcase and Resource Library

•Merchandise showcase with image galleries grouped by fandom and
category

•Upcoming releases section listing anticipated anime, games, movies,
shows, comics, and merchandise drops

•Backend-driven tagging (Example: 'Limited Edition,' 'Pre-Order,' or
'Collectible')

•Optional feature Admin can track view count and popularity of
merchandise and content items
Feedback and Analytics

•Dynamic feedback form with type categorization (bug, suggestion, or
query)

![Page 9 image](images/page-009-image-01.png)

### Bookmarking, Notes, and Sharing

•Bookmark any article, character profile, video, or merchandise item
Location-Aware Event Discovery and Calendar
•Map and GPS Integration: Users can discover nearby fan
conventions, cosplay meetups, and screening events using location
services.

•Event Calendar: Users can browse upcoming fandom conventions,
meetups, and screening schedules, filterable by city, with ticket links.

### Admin Control Panel

### Add/edit/remove:

•Category content (Anime, Gaming, Movies, TV Shows, K-Pop, Comics,
Manga, or Cosplay)

•Multimedia content, character profiles, and featured articles
•Optional Chatbot FAQ entries and knowledge base

---

© Aptech Limited

•User feedback and fan-submitted content
•View usage statistics: active users, popular categories, chatbot interaction
volume

### Accessibility and UI Enhancements

•Dark mode toggle and font-size adjustment for accessibility
•Breadcrumbs for navigation clarity across categories
•Smooth transitions and loading spinners for media-heavy pages

> Note: Boilerplate or readymade HTML template can be used, provided it is only for design
> aspect and not for implementing application functionality.

---

© Aptech Limited

Important Note Regarding AI Usage: solving skills.

```text
You are encouraged to use AI-powered tools (such as AI-assisted Website
builders, UI/UX design tools, code assistants, and image-generation tools) to
enhance productivity and creativity. However, AI should be used as a supporting
aid rather than a substitute for your own design, development, and problem-
```

Do NOT rely on completely ready-made Website templates for your project, as this will adversely affect your evaluation. Your Web application’ s design, structure, and implementation should primarily reflect your own skills and understanding. Do NOT submit AI-generated code or content without meaningful modification and understanding. AI-generated suggestions may be used for guidance, learning, debugging, or improving productivity, but the final solution should demonstrate your own effort, logic, and implementation.

Acknowledge all the AI tool (s) used (for example, Copilot, Canva AI, Figma AI,

Uizard, or similar) in your project documentation or submission.

During evaluation, judges may ask participants to explain their design decisions, implementation approach, and code. You are, therefore, expected to understand and be able to justify all aspects of your submitted work.

Do not use AI tools to fully produce ready-made documentation. This is strictly

forbidden.

Bottomline: AI is your assistant, not your developer. Your knowledge, creativity, and coding skills should drive the project.

# 1.7 Non-Functional Requirements

There are several non-functional requirements that should be fulfilled by the

### application. They include:

•Safe to use: The application should not result in any malicious downloads
or unnecessary file downloads.

•Accessibility: The application should have clear and legible fonts, user-
interface elements, and navigation elements.

•User-friendliness: The application should be easy to navigate with clear
menus and other elements and easy to understand.

---

© Aptech Limited

•Operability: The application should be reliable and efficient.
time and smooth page redirection.
feature expansions.
certain personalized features.
downtime.
and various devices.

•Performance: The application should demonstrate high value of
performance through speed and throughput, especially given the media-
rich content. In simple terms, the application should have minimal load

•Scalability: The application architecture and infrastructure should be
designed to handle increasing user traffic, growing content libraries, and

•Security: The application should implement adequate security measures
such as authentication. For example, only registered users can access

![Page 12 image](images/page-012-image-01.png)

•Availability: The application should be available 24/7 with minimum

•Compatibility: The application should be compatible with latest browsers

These are the bare minimum expectations from the project. It is a must to implement the FUNCTIONAL and NON-FUNCTIONAL requirements given in this SRS. Once they are complete, you can use your own creativity and imagination to add more features if

required.

---

© Aptech Limited

# 1.8 Interface Requirements Hardware Software

IDE: Appropriate IDE as per the platform Frontend: HTML 5, CSS 3, Bootstrap, React JS/Angular JS/Angular/Type Script, Java Script, j Query, and XML Backend: Java SDK with Apache Net Beans or Eclipse, Jakarta EE

*OR*

C# with ASP.NET MVC and ASP.NET MVC Core (optional), Visual Studio IDE

*OR*

### PHP with Laravel Framework

*OR*

### Python with Flask or Django

*OR*

### Mongo DB, Express.js, Angular, Node.js

*OR*

Mongo DB, Express.js, React, Node.js Database: My SQL/SQL Server/Mongo DB/JSON For local hosting (optional): XAMPP latest version AI Tools: Optional AI assistant/chatbot may be implemented using tools such as tawk.to or Zapier.

> Intel Core i5/i7 Processor or higher
> 8 GB RAM or higher
> Color SVGA monitor
> 500 GB Hard Disk space
> Mouse
> Keyboard

> SOFTWARE
> HARDWARE

---

© Aptech Limited

### Database Design

Based on the given specifications, you will define suitable entities, attributes for these entities, and identify relationships between the entities. For example, some entities along with their attributes, primary keys (PK), and foreign keys (FK) can be identified as follows:

•User- user_id (PK), name, email, password_hash, created_at
•Category- category_id (PK), name (Anime, Gaming, Movies, TV Shows, K-
Pop, Comics, Manga, Cosplay), description

•Content- content_id (PK), category_id (FK→Category.category_id), title,
type (article, video, audio, image), description, release_date,
popularity_score
•Character Profile- character_id (PK), category_id (FK→
Category.category_id), name, bio, image_url
•Merchandise Item- item_id (PK), category_id (FK→
Category.category_id), name, image_url, tag, is_upcoming
•Bookmark- bookmark_id (PK), user_id (FK→User.user_id), content_id (FK
→Content. Content_id), note, created_at

•Chatbot Query- query_id (PK), user_id (FK→User.user_id), message,
response, created_at

•Feedback- feedback_id (PK), user_id (FK→User.user_id), type, message,
status

Similarly, you can define other entities and relationships between entities and
methods representing activities on the entities.
Key Relationships:
•User→Category: many-to-many
•Category→Content, Category→Character Profile, Category→
Merchandise Item: one-to-many (one category groups many records of
each).

•User→Bookmark, User→Chatbot Query, User→Feedback: one-to-many
(one user can have many of each).

•Content→Bookmark: one-to-many (one content item can be
bookmarked by many users).

> Note: These are just examples, with primary keys (PK) and foreign keys (FK) identified to
> show referential integrity between tables. You do not have to adhere to these structures
> and can design your own table structure with different columns.

---

© Aptech Limited

# 1.9 Project Deliverables

You are required to design and build the project and submit it along with a
complete project report that includes:
•Problem Definition
•Design Specifications
•Diagrams such as Flowcharts for various Activities, Data Flow Diagrams,
and so on
•Database Design
•Test Data Used in the Project
•Project Installation Instructions (MANDATORY)
•User Credentials for all Types of Users with Passwords (MANDATORY)

Documentation is considered as a very important part of the project. Ensure that documentation is complete and comprehensive. Documentation should not contain any source code. The consolidated project will be submitted as a zip file with a Read Me.doc file listing assumptions (if any) made at your end and SQL script files (.sql) OR schema files containing database and table definitions. Note: Preferably, host the working Web application on a Website and share the URL for evaluation. Submit a video (.mp4 file) demonstrating the working of the Web application, including all features under Functional Requirements. This is MANDATORY. Over and above the given specifications, you can apply your creativity and logic to improve the system. Sitemap: To understand the flow of the Fan Hub Plus Web Application, you will have to create a Sitemap and add it to the home page of your application.

### ~~~Endof Document~~~