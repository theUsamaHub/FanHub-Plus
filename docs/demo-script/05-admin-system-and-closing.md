# Admin demonstration — Account, system tools, and closing

Continue after file 04. Read only **Narration** aloud. Optional pages follow the main closing and can be inserted before it if needed.

## 22. Profile — manage the administrator's account

**On screen:** Open the sidebar Profile link. Show profile details and password controls without displaying private information.

**Narration:**

Profile lets the administrator maintain their own account information and password. This is separate from managing platform content or inspecting other users. It keeps personal account settings in a familiar, dedicated location.

## 23. Activity Logs — trace recorded actions

**On screen:** Open Activity Logs, demonstrate filters, inspect one entry, and point to export.

**Narration:**

Activity Logs provides a history of actions recorded by the application. The list helps locate an event, and its detail page provides the associated information available for that action.

This is useful when investigating changes or understanding what happened in the system. Filters and export controls help the administrator work with that history.

## 24. Sessions — inspect access activity

**On screen:** Open Sessions. Point out session information and the revoke action without ending an active user's session.

**Narration:**

Sessions displays the session records available to the application, including associated users and recent activity. The administrator can revoke another session when necessary. The application prevents revoking the administrator's own current session from this control.

## 25. Maintenance — control temporary availability

**On screen:** Open Maintenance and show status, message, and bypass-route settings. Leave the live site's current availability unchanged.

**Narration:**

Maintenance provides controls for temporarily placing the website into maintenance mode. The administrator can manage the visitor-facing message and the routes allowed to bypass that mode.

The purpose is to communicate clearly while maintenance work is taking place, instead of leaving visitors unsure about the site's availability.

## 26. Logs — inspect application diagnostics

**On screen:** Open Logs with safe demonstration data. Show the available viewing, download, and clear controls without clearing the log.

**Narration:**

Logs supports technical investigation by displaying application log information. This differs from Activity Logs: activity records describe recorded actions, while application logs help investigate errors and operational problems.

The page also provides download and clear controls for managing that diagnostic information.

## 27. Backups — create database exports

**On screen:** Open Backups and show Create Backup plus the existing file list, download, and delete controls. Use only a local demonstration database if creating a backup on camera.

**Narration:**

Backups provides a way to create a database backup and manage the resulting files. The list shows existing backups with their size and date, alongside download and deletion controls.

This module exports database information. Uploaded media files need their own backup arrangement, and this page does not include a restore interface.

## Closing

**On screen:** Return to Dashboard, then briefly show the public item used earlier in the demonstration.

**Narration:**

This completes the administration workflow: categories and media provide the foundation, connected records power the public pages, moderation supports community contributions, and communication tools keep subscribers informed.

The dashboards and system pages help the administrator monitor and maintain the platform. Together, these features support the user experience demonstrated on the public side of FanHub Plus. Thank you for watching.

## Optional A. Settings — existing page, hidden sidebar link

**On screen:** Navigate to `/admin/settings` under the application's base URL. Show only non-sensitive values and the available edit/add controls.

**Narration:**

There is also a Site Settings page for grouped application settings. It provides controls for maintaining stored values, including the settings exposed by the current installation. Its sidebar link is currently hidden, so I am showing it separately from the normal navigation flow.

## Optional B. Notifications — existing page, hidden sidebar link

**On screen:** Open `/admin/notifications`. Show the list and read controls.

**Narration:**

The Notifications page lists the notifications available to this account. It supports marking individual notifications, or all notifications, as read, along with removal controls. This page exists even though its sidebar entry is currently hidden.

## Optional C. IP Restrictions — existing page, hidden sidebar link

**On screen:** Open `/admin/ip-restrictions`, point to the allowed-IP controls, and leave them unchanged.

**Narration:**

IP Restrictions provides an additional administration-access control through an allowed-IP list. It also shows the current address to help with configuration. The page exists separately from the visible sidebar flow, and changes here affect access to the panel.

## Optional D. Health — additional system dashboard

**On screen:** Open `/admin/health`. Show application, services, storage, queue, and system sections without exposing deployment details unnecessarily.

**Narration:**

The Health dashboard presents technical information about the running application. It includes service checks, storage information, queue details, and the application's environment. This complements the main dashboard and Analytics by focusing on the system supporting the website.
