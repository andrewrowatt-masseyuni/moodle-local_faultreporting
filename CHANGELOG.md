# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- [#31](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/29) - Fault report form can now be shown in a pop-up as well as on its own page. `local_faultreporting\output\popup_link` renders the link and loads the JavaScript, and is used by the companion `filter_faultreporting` plugin to expand its `{faultreport}` token
- [#29](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/29) - Add optional Course name, Course code, Activity name and URL fields to the fault report form, prefilled from the new optional `courseid` and `coursemoduleid` parameters
- [#18](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/18) - Add CSS to override Snap whitespace defaults on the fault reports UI to make it more compact
- [#19](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/19) - Use Moodle curl utility instead of custom curl implementation

### Fixed
- Fault report page no longer redirects to an arbitrary external URL supplied in the `url` parameter
- [#16](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/16) - Delete function not working on the fault reports admin screen

### Changed
- Fault report form is now a `core_form\dynamic_form`, so the standalone page and the pop-up share one form class, one payload builder and one set of description blocks. The `fromurl` and `diagnosticinfo` hidden fields have been removed; the diagnostic information is built when the report is submitted
- [#29](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/29) - Show username, name and email as static text on a single row with the editable phone field
- [#20](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/20) - Add Example context (json) to templates

## [1.0.7] - 2025-05-26

### Added
- [#1](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/1) - Interface to enable resend/retry of reports to Assyst
- [#2](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/2) - Staff "redirect" to AskUs with notice suggesting staff to go to AskUs directly
- [#3](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/3) - Handling for st accounts that do not have access to AskUs
- [#5](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/5) - Generate and add link to logs to case
- [#10](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/10) - Student-friendly description to faultreport.php
- [#12](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/12) - Review and update default description text for st accounts

### Fixed
- [#7](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/7) - Table with user field not covered with metadata provider (privacy provider implementation)
- [#9](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/9) - Remove hardcoded stream URL in faultreport.php
- [#11](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/11) - Form text misalignment in Snap theme

### Declined
- [#4](https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/issues/4) - Enable adding screenshots (marked as wontfix)

[Unreleased]: https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/compare/v1.0.7...HEAD
[1.0.7]: https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/releases/tag/v1.0.7
