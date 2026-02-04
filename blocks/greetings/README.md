# Greetings #

A block to post greeting messages on the Dashboard.

This block is a port of the [local_greetings](https://github.com/moodleacademy/moodle-local_greetings) plugin. This is a sample plugin created for use in Moodle Academy courses.

## Adding the Greetings block to the Dashboard ##

1. Log in and go to your **Dashboard** (My Moodle), e.g. via the user menu or _Site home > Dashboard_.
2. Turn **editing on** (click "Customise this page" or the edit/pencil icon).
3. In the "Add a block" block (or block drawer), choose **Greetings**.
4. The block appears on your Dashboard. You can drag it to another region if you like.
5. Turn editing off when finished.

You need the capability **Add a new Greetings block to Dashboard** (`block/greetings:myaddinstance`); by default, logged-in users have this on their Dashboard.

## How it works (AJAX form) ##

- **Form submission and processing is handled via JavaScript and AJAX** using Moodle's dynamic form and modal:
  1. Clicking **Add message** opens a modal dialogue. The form is loaded and submitted via the **core_form_dynamic_form** web service (no full page reload).
  2. The form class `block_greetings\\form\\message_form` extends `\core_form\dynamic_form`. When the user submits in the modal, the web service calls `process_dynamic_submission()`, which saves the message to `block_greetings_messages` and returns the new message data (for the card) as JSON.
  3. The AMD module `block_greetings/addmessage` listens for `FORM_SUBMITTED`, then appends the new message card to the list in the block (no page reload).
- **Deleting a message** is still done in PHP: a link adds `?action=del&id=...&sesskey=...` to the Dashboard URL; the page reloads after delete.

## Building the JavaScript (AMD) ##

If the block's "Add message" button does nothing or the modal does not open, build the AMD module:

    npx grunt amd --root=blocks/greetings

(or from the Moodle root: `npx grunt amd` to build all). This generates `amd/build/addmessage.min.js` from `amd/src/addmessage.js`.

## Exploring the code ##

| File | Purpose |
|------|---------|
| `block_greetings.php` | Block class: `get_content()` builds the block HTML, adds an "Add message" button (no inline form), loads messages from `block_greetings_messages`, and requires the `block_greetings/addmessage` AMD module. Delete is still handled via `optional_param('action')` and redirect. |
| `classes/form/message_form.php` | **Dynamic form** (extends `\core_form\dynamic_form`): used in the modal; implements `get_context_for_dynamic_submission`, `check_access_for_dynamic_submission`, `process_dynamic_submission` (saves message and returns new message data for JS), `set_data_for_dynamic_submission`, `get_page_url_for_dynamic_submission`. |
| `amd/src/addmessage.js` | AMD module: on "Add message" click, creates a `ModalForm` (core_form/modalform) with form class `block_greetings\\form\\message_form`; on FORM_SUBMITTED, appends the new message card to the messages container. |
| `lib.php` | `block_greetings_get_greeting($user)` returns a greeting string (e.g. by user country). |
| `templates/greeting_message.mustache` | Renders the greeting line at the top of the block. |
| `templates/messages.mustache` | Renders the list of message cards (and delete/edit links). |
| `db/access.php` | Capabilities: `myaddinstance`, `postmessages`, `viewmessages`, `deleteownmessage`, `deleteanymessage`. |
| `db/install.xml` | Defines the `block_greetings_messages` table. |

The block uses the core web service **core_form_dynamic_form** (no custom service in the block). The form is loaded and submitted via AJAX; the PHP form class must extend `\core_form\dynamic_form`.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/blocks/greetings

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2023 Your Name <you@example.com>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
