# Security Policy

## Reporting Security Vulnerabilities

We take security seriously. If you discover a security vulnerability in XBuilder, please report it responsibly.

### How to Report

**Please DO NOT open a public issue for security vulnerabilities.**

Instead, email security details to:
- **Email**: [Your security contact email]
- **Subject**: `[SECURITY] XBuilder Vulnerability Report`

### What to Include

1. Description of the vulnerability
2. Steps to reproduce
3. Potential impact
4. Suggested fix (if you have one)

### Response Timeline

- We will acknowledge your report within 48 hours
- We will provide a detailed response within 7 days
- We will work on a fix and keep you updated on progress

### Disclosure Policy

- Please give us reasonable time to fix the issue before public disclosure
- We will credit you in the release notes (unless you prefer to remain anonymous)
- We appreciate responsible disclosure

## Security Best Practices

When deploying XBuilder:

1. **Use HTTPS** - Always use SSL/TLS in production
2. **Strong Passwords** - Use complex admin passwords (12+ characters)
3. **Keep Updated** - Update to the latest version regularly
4. **Restrict Access** - Limit `/xbuilder/` admin access if possible
5. **Environment Variables** - Store sensitive data in environment variables (not files)

## Security Features

XBuilder includes:
- AES-256-CBC encryption for API keys
- Argon2id password hashing
- CSRF protection
- Session security with IP validation
- Account lockout after failed login attempts
- Security audit logging

---

For general questions, visit: https://github.com/Asif2BD/xBuilderCMS
