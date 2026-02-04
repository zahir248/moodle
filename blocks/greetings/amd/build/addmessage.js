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

define([
    'core_form/modalform',
    'core/str',
    'core/notification'
], function(ModalForm, Str, Notification) {
    var FORM_CLASS = 'block_greetings\\form\\message_form';

    /**
     * Escape HTML for safe insertion.
     *
     * @param {string} text Raw text.
     * @returns {string} Escaped HTML.
     */
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Build HTML for one message card.
     *
     * @param {Object} data Message data from process_dynamic_submission.
     * @returns {string} HTML string for the card.
     */
    function buildMessageCard(data) {
        var footer = '';
        if (data.candelete) {
            footer = '<p class="card-footer text-center">' +
                '<a href="' + escapeHtml(data.deleteurl) + '" role="button">Delete</a> ' +
                '<a href="' + escapeHtml(data.editurl) + '" role="button">Edit</a>' +
                '</p>';
        }
        var bgcolor = data.cardbackgroundcolor || '#fff';
        return '<div class="card" style="background: ' + escapeHtml(bgcolor) + '" role="listitem">' +
            '<div class="card-body">' +
            '<p class="card-text">' + escapeHtml(data.message) + '</p>' +
            '<p class="card-text">Posted by ' + escapeHtml(data.authorname) + '.</p>' +
            '<p class="card-text"><small class="text-muted">' + escapeHtml(data.formatteddate) + '</small></p>' +
            footer +
            '</div></div>';
    }

    /**
     * Initialise Add message button: open modal form, on submit append new message to the list.
     *
     * @param {string} addMessageButtonId ID of the "Add message" button.
     * @param {string} messagesContainerId ID of the messages list container.
     */
    function init(addMessageButtonId, messagesContainerId) {
        var button = document.getElementById(addMessageButtonId);
        if (!button) {
            return;
        }

        button.addEventListener('click', function(e) {
            e.preventDefault();
            Str.get_string('addmessage', 'block_greetings').then(function(title) {
                var form = new ModalForm({
                    formClass: FORM_CLASS,
                    args: {},
                    modalConfig: {title: title},
                    returnFocus: e.target
                });

                form.addEventListener(form.events.FORM_SUBMITTED, function(event) {
                    var data = event.detail;
                    if (data.error) {
                        Notification.addNotification({
                            message: data.error,
                            type: 'error'
                        });
                        return;
                    }
                    var container = document.getElementById(messagesContainerId);
                    if (container) {
                        var card = document.createElement('div');
                        card.innerHTML = buildMessageCard(data);
                        container.insertBefore(card.firstElementChild, container.firstChild);
                    }
                });

                form.show();
            }).catch(Notification.exception);
        });
    }

    return {
        init: init
    };
});
