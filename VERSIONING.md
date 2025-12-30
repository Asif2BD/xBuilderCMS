# XBuilder Versioning Guide

Complete guide for managing versions in XBuilder following Semantic Versioning.

---

## 📖 Table of Contents

- [Semantic Versioning Explained](#semantic-versioning-explained)
- [When to Bump Version](#when-to-bump-version)
- [Using bump-version.sh](#using-bump-versionsh)
- [Manual Version Bump](#manual-version-bump)
- [Automated Releases](#automated-releases)
- [Version Tracking](#version-tracking)

---

## 📋 Semantic Versioning Explained

XBuilder follows [Semantic Versioning 2.0.0](https://semver.org/):

```
MAJOR.MINOR.PATCH

Example: 0.3.0
         │ │ │
         │ │ └─ PATCH version (bug fixes)
         │ └─── MINOR version (new features)
         └───── MAJOR version (breaking changes)
```

### Version Types

#### PATCH (0.3.0 → 0.3.1)
**For bug fixes and minor improvements**

Examples:
- Fixed Gemini API compatibility issue
- Resolved 403 directory access error
- Performance improvements
- Documentation typo fixes
- Security patches

```bash
./bump-version.sh patch
```

#### MINOR (0.3.0 → 0.4.0)
**For new features (backward compatible)**

Examples:
- Multi-server support (Apache, Nginx, OpenLiteSpeed)
- Version display in UI
- New export formats
- Additional AI provider support
- New configuration options

```bash
./bump-version.sh minor
```

#### MAJOR (0.9.0 → 1.0.0)
**For breaking changes**

Examples:
- API redesign (breaking existing integrations)
- Database schema changes
- Configuration file format changes
- Removed deprecated features
- First stable release (0.x → 1.0.0)

```bash
./bump-version.sh major
```

---

## 🎯 When to Bump Version

### Always Bump When:
- ✅ Merging a feature branch to main
- ✅ Fixing a bug
- ✅ Adding new functionality
- ✅ Making breaking changes
- ✅ Security patches

### Don't Bump For:
- ❌ README updates only
- ❌ Comment changes
- ❌ Reformatting code (no logic changes)
- ❌ CI/CD configuration updates
- ❌ .gitignore changes

### Multiple Changes in One Release:
Use the **highest** version bump type:
- Bug fixes + new feature = **MINOR** bump
- New features + breaking change = **MAJOR** bump
- Multiple bug fixes = **PATCH** bump

---

## 🚀 Using bump-version.sh

The automated script makes version bumping easy!

### Interactive Mode (Recommended)

```bash
# Navigate to project root
cd /path/to/xBuilderCMS

# Run the script
./bump-version.sh [patch|minor|major]
```

### Step-by-Step Process:

#### 1. Run the Script
```bash
./bump-version.sh minor
```

Output:
```
Current version: 0.3.0
New version: 0.4.0
Bump version from 0.3.0 to 0.4.0? (y/n)
```

#### 2. Confirm Version Bump
```
y
✓ Updated VERSION file
✓ Updated README.md
```

#### 3. Update CHANGELOG.md
```
Update CHANGELOG.md manually before committing!
Add your changes under:
  ## [0.4.0] - 2025-12-30

Categories: Added, Changed, Deprecated, Removed, Fixed, Security

Press Enter when CHANGELOG.md is updated...
```

**Edit CHANGELOG.md:**
```markdown
## [0.4.0] - 2025-12-30

### Added
- Image upload and optimization feature
- Custom domain configuration helper

### Changed
- Improved AI response parsing
```

Press Enter when done.

#### 4. Review Changes
```
Files staged for commit:
  VERSION
  README.md
  CHANGELOG.md

Commit version bump? (y/n)
```

#### 5. Commit
```
y
✓ Committed version bump
```

#### 6. Create Tag
```
Create git tag v0.4.0? (y/n)
```

```
y
✓ Created tag v0.4.0

Push to remote (branch + tag)? (y/n)
```

#### 7. Push to Remote
```
y
✓ Pushed to remote

Version bump complete!
Version: 0.3.0 → 0.4.0
```

---

## 📝 Manual Version Bump

If you prefer manual control or the script isn't available:

### 1. Update VERSION File
```bash
echo "0.4.0" > VERSION
```

### 2. Update README.md

**Update version badge:**
```markdown
[![Version](https://img.shields.io/badge/version-0.4.0-blue.svg)]
```

**Update "Current Version":**
```markdown
> 🚀 **Current Version: 0.4.0** |
```

**Update "Version History" section:**
```markdown
**Current Version: 0.4.0**

### Version History

**v0.4.0** (2025-12-30) - Feature Name
- ✅ New feature description
```

### 3. Update CHANGELOG.md

Add new version at the top:
```markdown
## [0.4.0] - 2025-12-30

### Added
- Feature description

### Changed
- Change description

### Fixed
- Bug fix description
```

### 4. Update Config.php Fallback

Edit `xbuilder/core/Config.php`:
```php
return '0.4.0'; // Fallback version
```

### 5. Commit and Tag
```bash
git add VERSION README.md CHANGELOG.md xbuilder/core/Config.php
git commit -m "chore: bump version to 0.4.0"
git tag -a v0.4.0 -m "Release version 0.4.0"
git push origin main
git push origin v0.4.0
```

---

## 🤖 Automated Releases

### GitHub Actions Workflow

The `.github/workflows/version-tag.yml` workflow automatically:
1. Detects VERSION file changes on main branch
2. Creates a git tag (e.g., `v0.4.0`)
3. Creates a GitHub Release
4. Links to CHANGELOG.md

**Trigger:** Push to main branch with VERSION file change

### Creating a Release Manually

If GitHub Actions doesn't run or you're on a different branch:

```bash
# After bumping version
git tag -a v0.4.0 -m "Release version 0.4.0"
git push origin v0.4.0

# Then create release on GitHub:
gh release create v0.4.0 \
  --title "XBuilder v0.4.0" \
  --notes "See CHANGELOG.md for details"
```

---

## 📊 Version Tracking

### Where Version is Stored

1. **VERSION file** (single source of truth)
   ```
   0.3.0
   ```

2. **Config.php** (app reads this)
   ```php
   public function getAppVersion(): string
   {
       $versionFile = dirname(__DIR__, 2) . '/VERSION';
       return trim(file_get_contents($versionFile));
   }
   ```

3. **User's config** (installation record)
   ```json
   {
     "version": "0.3.0",
     "created_at": "2025-12-30"
   }
   ```

### Checking Version

**In PHP:**
```php
$config = new XBuilder\Core\Config();
$version = $config->getAppVersion();
echo "Running XBuilder v" . $version;
```

**In UI:**
All admin pages display version in footer:
```html
<div>XBuilder v0.3.0</div>
```

**Command line:**
```bash
cat VERSION
# Output: 0.3.0
```

---

## 🔄 Version Upgrade Flow

When users upgrade XBuilder:

### User's Perspective
1. User installs v0.2.0
2. `config.json` stores `"version": "0.2.0"`
3. User upgrades code to v0.3.0
4. App detects new version via VERSION file
5. (Future) Migration scripts run automatically

### Implementation (Future)
```php
public function needsUpgrade(): bool
{
    $installed = $this->getInstalledVersion(); // "0.2.0"
    $current = $this->getAppVersion();         // "0.3.0"
    return version_compare($current, $installed, '>');
}
```

---

## 🎓 Best Practices

### DO:
- ✅ Bump version for every release
- ✅ Update CHANGELOG.md with details
- ✅ Use semantic versioning rules
- ✅ Test before bumping version
- ✅ Create git tags for releases
- ✅ Keep VERSION file in sync with README

### DON'T:
- ❌ Skip versions (0.3.0 → 0.5.0)
- ❌ Reuse version numbers
- ❌ Forget to update CHANGELOG
- ❌ Bump version for docs-only changes
- ❌ Make breaking changes in PATCH versions

---

## 📚 Examples

### Example 1: Bug Fix

**Scenario:** Fixed storage permission issue

```bash
./bump-version.sh patch

# Updates: 0.3.0 → 0.3.1

# CHANGELOG.md:
## [0.3.1] - 2025-12-31

### Fixed
- Storage directory permission error on fresh installations
```

### Example 2: New Feature

**Scenario:** Added image upload support

```bash
./bump-version.sh minor

# Updates: 0.3.1 → 0.4.0

# CHANGELOG.md:
## [0.4.0] - 2026-01-15

### Added
- Image upload and optimization feature
- Support for PNG, JPEG, WebP formats
- Automatic image compression
```

### Example 3: Breaking Change

**Scenario:** Redesigned API endpoints

```bash
./bump-version.sh major

# Updates: 0.4.0 → 1.0.0 🎉

# CHANGELOG.md:
## [1.0.0] - 2026-03-01

### Changed
- **BREAKING**: API endpoints now use /v2/ prefix
- **BREAKING**: Configuration file format updated

### Migration Guide
See UPGRADE.md for migration instructions
```

---

## 🆘 Troubleshooting

### Version Out of Sync

If VERSION, README, and CHANGELOG don't match:

```bash
# Check current values
cat VERSION
grep "Current Version" README.md
grep "## \[" CHANGELOG.md | head -1

# Fix with script
./bump-version.sh patch  # Will sync everything
```

### Script Not Executable

```bash
chmod +x bump-version.sh
```

### Merge Conflicts on VERSION

```bash
# Accept the higher version number
# If both branches bumped from 0.3.0:
# - Branch A: 0.4.0
# - Branch B: 0.3.1
# Use: 0.4.0 (higher MINOR beats PATCH)
```

---

## 📞 Questions?

- **Issues**: [GitHub Issues](https://github.com/Asif2BD/xBuilderCMS/issues)
- **Discussions**: [GitHub Discussions](https://github.com/Asif2BD/xBuilderCMS/discussions)
- **Contributing**: See [CONTRIBUTING.md](CONTRIBUTING.md)

---

**Made with ❤️ by Asif Rahman** | *Powered by xCloud.host*
