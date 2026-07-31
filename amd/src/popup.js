// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Opens the fault report form in a pop-up.
 *
 * The links are rendered by local_faultreporting\output\popup_link, which may be anywhere on the
 * page, so the listener is delegated from the document.
 *
 * @module     local_faultreporting/popup
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';
import {add as addToast} from 'core/toast';

/** @constant {Object} The object containing the relevant selectors. */
const Selectors = {
    faultReportLink: '[data-action="local_faultreporting-faultreport"]',
};

/** @constant {String} Name of the form class, which must extend \core_form\dynamic_form. */
const formClass = 'local_faultreporting\\form\\faultreport';

/** @type {Boolean} Whether the listener has already been registered. */
let initialised = false;

/**
 * Open the fault report form in a modal.
 *
 * @param {HTMLElement} link The link that was used.
 * @returns {Promise} Resolves once the modal is shown.
 */
const showFaultReport = async(link) => {
    // Loaded on demand, as most pages the links could appear on will never show one.
    const ModalForm = await import('core_form/modalform');

    const modalForm = new ModalForm({
        formClass,
        args: {
            nosubmit: 1,
            contextid: link.dataset.contextid,
            courseid: link.dataset.courseid,
            coursemoduleid: link.dataset.coursemoduleid,
            // Read from the browser rather than the link, as filtered text may have been cached.
            page: document.title,
            url: window.location.href,
        },
        modalConfig: {title: getString('createnewfaultreport', 'local_faultreporting')},
        saveButtonText: getString('submitreport', 'local_faultreporting'),
        returnFocus: link,
    });

    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e) => {
        addToast(e.detail.message, {
            type: 'info',
        });
    });

    return modalForm.show();
};

/**
 * Register the event listener for the fault report links.
 */
const registerEventListeners = () => {
    document.addEventListener('click', (e) => {
        const link = e.target.closest(Selectors.faultReportLink);

        if (link) {
            e.preventDefault();
            showFaultReport(link);
        }
    });
};

/**
 * Initialise the module.
 *
 * Fragments get their own requirements manager, so this can be called more than once on a page.
 */
export const init = () => {
    if (initialised) {
        return;
    }

    initialised = true;
    registerEventListeners();
};
