// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Handle Add message button: open modal form and append new message via AJAX.
 *
 * @module     block_greetings/addmessage
 * @copyright  2026 Intensiti Elemen Sdn Bhd
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
import {getString} from 'core/str';
import Notification from 'core/notification';

const FORM_CLASS = 'block_greetings\\form\\message_form';

/**
 * Build HTML for one message card (matches block_greetings/messages template structure).
 *
 * @param {Object} data Message data from process_dynamic_submission.
 * @returns {string} HTML string for the card.
 */
function buildMessageCard(data) {
    let footer = '';
    if (data.candelete) {
        footer = `<p class="card-footer text-center">
            <a href="${escapeHtml(data.deleteurl)}" role="button">Delete</a>
            <a href="${escapeHtml(data.editurl)}" role="button">Edit</a>
        </p>`;
    }
    const bgcolor = data.cardbackgroundcolor || '#fff';
    return `<div class="card" style="background: ${escapeHtml(bgcolor)}" role="listitem">
        <div class="card-body">
            <p class="card-text">${escapeHtml(data.message)}</p>
            <p class="card-text">Posted by ${escapeHtml(data.authorname)}.</p>
            <p class="card-text"><small class="text-muted">${escapeHtml(data.formatteddate)}</small></p>
            ${footer}
        </div>
    </div>`;
}

/**
 * Escape HTML for safe insertion.
 *
 * @param {string} text Raw text.
 * @returns {string} Escaped HTML.
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Initialise Add message button: open modal form, on submit append new message to the list.
 *
 * @param {string} addMessageButtonId ID of the "Add message" button.
 * @param {string} messagesContainerId ID of the messages list container.
 */
export const init = (addMessageButtonId, messagesContainerId) => {
    const button = document.getElementById(addMessageButtonId);
    if (!button) {
        return;
    }

    button.addEventListener('click', (e) => {
        e.preventDefault();
        getString('addmessage', 'block_greetings').then((title) => {
            const form = new ModalForm({
                formClass: FORM_CLASS,
                args: {},
                modalConfig: {title},
                returnFocus: e.target,
            });

            form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                const data = event.detail;
                if (data.error) {
                    Notification.addNotification({
                        message: data.error,
                        type: 'error',
                    });
                    return;
                }
                const container = document.getElementById(messagesContainerId);
                if (container) {
                    const card = document.createElement('div');
                    card.innerHTML = buildMessageCard(data);
                    container.insertBefore(card.firstElementChild, container.firstChild);
                }
            });

            form.show();
        }).catch(Notification.exception);
    });
};
