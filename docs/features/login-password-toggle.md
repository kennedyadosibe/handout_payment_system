# Login Password Toggle

## Purpose

The admin login form now lets users show or hide the password while typing. This helps prevent login mistakes when entering longer passwords.

## How To Use

1. Open the admin login page.
2. Type a password.
3. Click `Show` to reveal the password.
4. Click `Hide` to obscure it again.

## Files Changed

- `admin/login.php`
- `assets/css/styles.css`

## Official Documentation Checked

- MDN password input documentation: https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/password

## Testing Notes

- Ran PHP syntax checks for `admin/login.php`.
- Verified the button changes the password field between `password` and `text`.
