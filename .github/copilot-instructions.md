# Copilot Instructions for This WordPress Codebase

## Overview
This is a customized WordPress project. It includes core WordPress files, custom frontend logic, and uses Tailwind CSS for styling. The project structure and workflows differ from a stock WordPress install.

## Key Architecture
- **Core**: Standard WordPress files in the root, `wp-admin/`, `wp-includes/`, and `wp-content/`.
- **Custom Frontend**: All custom guide logic is in `frontend/`:
  - `dynamic-steps.php`: Dynamically fetches steps from custom post type `spg_step`.
  - `hardcoded-stepsphp`: Contains fallback hardcoded steps for the guide.
  - `current-guide-state.php`: Handles AJAX for getting/setting user guide state (uses user meta).
  - `modal.php`: Renders the guide modal UI.
  - `shortcodes.php`: AJAX and shortcode logic, e.g., `[mm_spg_interest_form]`.
- **Assets**: Tailwind CSS is managed via `package.json` dependencies. No build scripts are present—run Tailwind CLI manually if needed.
- **Database**: Uses a MySQL database (see `wp-config.php`). Host is `db`, not `localhost` (likely for Docker/local dev).

## Developer Workflows
- **No build step**: Tailwind is included via CLI, not automated. Run `npx tailwindcss ...` as needed.
- **Testing**: No automated tests or test runner are present.
- **Debugging**: Use standard WordPress debugging (e.g., `WP_DEBUG` in `wp-config.php`).
- **AJAX**: All AJAX endpoints are registered via `add_action('wp_ajax_...')` in custom PHP files.

## Project Conventions
- **Custom logic is isolated in `frontend/`**. Do not add custom code to core WordPress files.
- **AJAX endpoints**: Always check `is_user_logged_in()` before processing.
- **User state**: Guide progress is tracked in user meta fields (e.g., `mm_spg_status`, `mm_spg_step`).
- **Steps**: Prefer dynamic steps from the `spg_step` post type, fallback to hardcoded if needed.
- **Shortcodes**: Use `add_shortcode` and AJAX for dynamic form rendering.

## Integration Points
- **Tailwind CSS**: Managed via `@tailwindcss/cli` and `tailwindcss` in `package.json`.
- **WordPress AJAX**: All custom AJAX handled via `wp_ajax_` hooks and `wp_send_json_success/error`.
- **No external build tools**: No Webpack, Gulp, or similar—keep asset management simple.

## Examples
- See `frontend/dynamic-steps.php` for dynamic step loading pattern.
- See `frontend/current-guide-state.php` for user meta AJAX handling.
- See `frontend/shortcodes.php` for AJAX + shortcode integration.

---

For any new features, follow the pattern of isolating custom logic in `frontend/` and using WordPress hooks and AJAX for communication.
