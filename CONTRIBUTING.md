# Contributing to XBuilder

First off, thank you for considering contributing to XBuilder! 🎉

The following is a set of guidelines for contributing to XBuilder. These are mostly guidelines, not rules. Use your best judgment, and feel free to propose changes to this document in a pull request.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Workflow](#development-workflow)
- [Versioning Guidelines](#versioning-guidelines)
- [Commit Message Guidelines](#commit-message-guidelines)
- [Pull Request Process](#pull-request-process)

---

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to [your-email@example.com].

---

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the [existing issues](https://github.com/Asif2BD/xBuilderCMS/issues) as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples** (code snippets, screenshots)
- **Describe the behavior you observed** and what you expected
- **Include details about your environment** (OS, PHP version, web server)

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

- **Use a clear and descriptive title**
- **Provide a detailed description** of the suggested enhancement
- **Explain why this enhancement would be useful**
- **List any alternative solutions** you've considered

### Your First Code Contribution

Unsure where to begin? You can start by looking through these issues:

- `good-first-issue` - Issues that should only require a few lines of code
- `help-wanted` - Issues that might be a bit more involved

---

## Development Workflow

### Setting Up Development Environment

```bash
# Clone the repository
git clone https://github.com/Asif2BD/xBuilderCMS.git
cd xBuilderCMS

# Install on local PHP server
php -S localhost:8000

# Visit http://localhost:8000
```

### Project Structure

```
xBuilderCMS/
├── xbuilder/core/      # Core PHP classes
├── xbuilder/api/       # API endpoints
├── xbuilder/views/     # UI templates
├── site/               # Generated websites
└── xbuilder/storage/   # User data (gitignored)
```

### Running Tests

```bash
# Coming soon - test suite in development
php vendor/bin/phpunit
```

---

## Versioning Guidelines

XBuilder follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html) (SemVer):

```
MAJOR.MINOR.PATCH

Example: 0.2.0
         │ │ │
         │ │ └─ PATCH: Bug fixes (backward compatible)
         │ └─── MINOR: New features (backward compatible)
         └───── MAJOR: Breaking changes (not backward compatible)
```

### Version Numbering Rules

#### Pre-1.0.0 (Current Development Phase)

- **0.x.y** = Development/Beta phase
- **MAJOR (0)** = Stays at 0 until stable release
- **MINOR (x)** = New features, significant changes
- **PATCH (y)** = Bug fixes, minor improvements

#### When to Bump Versions

**PATCH (0.2.0 → 0.2.1)**
- Bug fixes
- Performance improvements
- Documentation updates
- Minor UI tweaks
- Dependency updates

**MINOR (0.2.0 → 0.3.0)**
- New features
- New AI provider support
- New export formats
- New configuration options
- Significant UI improvements
- New web server support

**MAJOR (0.9.0 → 1.0.0)**
- Breaking API changes
- Database schema changes
- Major architecture refactoring
- First stable release (0.x → 1.0.0)

### How to Bump Version

1. **Update VERSION file**
   ```bash
   echo "0.3.0" > VERSION
   ```

2. **Update CHANGELOG.md**
   ```markdown
   ## [0.3.0] - 2025-12-31

   ### Added
   - New feature description

   ### Changed
   - Changed feature description

   ### Fixed
   - Bug fix description
   ```

3. **Update README.md badges**
   ```markdown
   [![Version](https://img.shields.io/badge/version-0.3.0-blue.svg)]
   ```

   And update the "Current Version" and "Recent Updates" sections.

4. **Commit with version bump message**
   ```bash
   git add VERSION CHANGELOG.md README.md
   git commit -m "chore: bump version to 0.3.0"
   ```

5. **Tag the release** (after merging to main)
   ```bash
   git tag -a v0.3.0 -m "Release version 0.3.0"
   git push origin v0.3.0
   ```

### Automated Version Tagging

When you merge to `main` and the VERSION file changes, GitHub Actions automatically:
- Creates a git tag (e.g., `v0.3.0`)
- Creates a GitHub Release
- Links to the CHANGELOG

---

## Commit Message Guidelines

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- **feat**: A new feature (triggers MINOR version bump)
- **fix**: A bug fix (triggers PATCH version bump)
- **docs**: Documentation only changes
- **style**: Code style changes (formatting, semicolons, etc.)
- **refactor**: Code refactoring (no functional changes)
- **perf**: Performance improvements
- **test**: Adding or updating tests
- **chore**: Maintenance tasks (dependencies, build tools)
- **ci**: CI/CD changes

### Examples

```bash
# New feature
git commit -m "feat(ai): add support for Anthropic Claude 3.5"

# Bug fix
git commit -m "fix(auth): resolve session timeout issue"

# Documentation
git commit -m "docs(readme): update installation instructions"

# Breaking change
git commit -m "feat(api)!: redesign chat API endpoints

BREAKING CHANGE: Chat API endpoints now use /v2/ prefix"
```

---

## Pull Request Process

### Before Submitting

1. **Test your changes** locally
2. **Update documentation** if needed
3. **Add tests** for new features (when test suite is ready)
4. **Update CHANGELOG.md** with your changes
5. **Follow code style** (PSR-12 for PHP)

### Submitting a Pull Request

1. **Fork the repository** and create your branch from `main`
   ```bash
   git checkout -b feature/amazing-feature
   ```

2. **Make your changes** with clear, atomic commits

3. **Push to your fork**
   ```bash
   git push origin feature/amazing-feature
   ```

4. **Open a Pull Request** with:
   - Clear title describing the change
   - Description of what changed and why
   - Reference to related issues (e.g., "Fixes #123")
   - Screenshots for UI changes
   - Checklist of what you've done

### Pull Request Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix (PATCH)
- [ ] New feature (MINOR)
- [ ] Breaking change (MAJOR)
- [ ] Documentation update

## Checklist
- [ ] Code follows PSR-12 style guidelines
- [ ] Self-reviewed my own code
- [ ] Commented hard-to-understand areas
- [ ] Updated documentation
- [ ] Updated CHANGELOG.md
- [ ] Added tests (when available)
- [ ] All tests pass
- [ ] Bumped version if needed

## Related Issues
Fixes #(issue number)

## Screenshots (if applicable)
```

### Review Process

1. At least one maintainer must approve
2. All CI checks must pass
3. No merge conflicts
4. Documentation updated
5. CHANGELOG updated

---

## Code Style Guidelines

### PHP (PSR-12)

```php
<?php

namespace XBuilder\Core;

class Example
{
    private string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    public function doSomething(): void
    {
        // Code here
    }
}
```

### Documentation

- Use PHPDoc for all classes and public methods
- Explain complex logic with inline comments
- Keep README.md up to date
- Update DEPLOYMENT.md for infrastructure changes

---

## Release Process (Maintainers Only)

1. **Prepare Release**
   - Update VERSION file
   - Update CHANGELOG.md with all changes
   - Update README.md badges and recent updates
   - Create PR titled "chore: release v0.x.0"

2. **Merge to Main**
   - Squash merge if multiple commits
   - Use commit message: "chore: release v0.x.0"

3. **Tag Release**
   - GitHub Actions will auto-create tag and release
   - Or manually: `git tag -a v0.x.0 -m "Release v0.x.0"`

4. **Announce Release**
   - Post in Discussions
   - Update documentation site
   - Notify community

---

## Questions?

- **Discussions**: [GitHub Discussions](https://github.com/Asif2BD/xBuilderCMS/discussions)
- **Issues**: [GitHub Issues](https://github.com/Asif2BD/xBuilderCMS/issues)
- **Email**: [your-email@example.com]

---

Thank you for contributing to XBuilder! 🚀

**Made with ❤️ by the XBuilder Community**
