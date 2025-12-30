#!/bin/bash
# XBuilder Version Bump Script
# Usage: ./bump-version.sh [major|minor|patch]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get current version
CURRENT_VERSION=$(cat VERSION)
echo -e "${YELLOW}Current version: $CURRENT_VERSION${NC}"

# Parse version
IFS='.' read -r MAJOR MINOR PATCH <<< "$CURRENT_VERSION"

# Determine new version
case "$1" in
    major)
        MAJOR=$((MAJOR + 1))
        MINOR=0
        PATCH=0
        ;;
    minor)
        MINOR=$((MINOR + 1))
        PATCH=0
        ;;
    patch)
        PATCH=$((PATCH + 1))
        ;;
    *)
        echo -e "${RED}Error: Invalid argument. Use 'major', 'minor', or 'patch'${NC}"
        echo "Usage: ./bump-version.sh [major|minor|patch]"
        echo ""
        echo "Examples:"
        echo "  ./bump-version.sh patch  # 0.3.0 -> 0.3.1 (bug fixes)"
        echo "  ./bump-version.sh minor  # 0.3.0 -> 0.4.0 (new features)"
        echo "  ./bump-version.sh major  # 0.3.0 -> 1.0.0 (breaking changes)"
        exit 1
        ;;
esac

NEW_VERSION="$MAJOR.$MINOR.$PATCH"
echo -e "${GREEN}New version: $NEW_VERSION${NC}"

# Confirm with user
read -p "Bump version from $CURRENT_VERSION to $NEW_VERSION? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

# Update VERSION file
echo "$NEW_VERSION" > VERSION
echo -e "${GREEN}✓${NC} Updated VERSION file"

# Update README.md version badge
sed -i.bak "s/version-[0-9]*\.[0-9]*\.[0-9]*-blue/version-$NEW_VERSION-blue/g" README.md
sed -i.bak "s/\*\*Current Version: [0-9]*\.[0-9]*\.[0-9]*\*\*/**Current Version: $NEW_VERSION**/g" README.md
rm -f README.md.bak
echo -e "${GREEN}✓${NC} Updated README.md"

# Prompt for changelog entry
echo ""
echo -e "${YELLOW}Update CHANGELOG.md manually before committing!${NC}"
echo "Add your changes under:"
echo "  ## [$NEW_VERSION] - $(date +%Y-%m-%d)"
echo ""
echo "Categories: Added, Changed, Deprecated, Removed, Fixed, Security"
echo ""
read -p "Press Enter when CHANGELOG.md is updated..."

# Stage files
git add VERSION README.md CHANGELOG.md

# Show what changed
echo ""
echo -e "${YELLOW}Files staged for commit:${NC}"
git diff --cached --stat

# Confirm commit
echo ""
read -p "Commit version bump? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted. Files are staged but not committed."
    exit 1
fi

# Commit
git commit -m "chore: bump version to $NEW_VERSION"
echo -e "${GREEN}✓${NC} Committed version bump"

# Ask about tagging
echo ""
read -p "Create git tag v$NEW_VERSION? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    git tag -a "v$NEW_VERSION" -m "Release version $NEW_VERSION"
    echo -e "${GREEN}✓${NC} Created tag v$NEW_VERSION"

    read -p "Push to remote (branch + tag)? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        BRANCH=$(git rev-parse --abbrev-ref HEAD)
        git push origin "$BRANCH"
        git push origin "v$NEW_VERSION"
        echo -e "${GREEN}✓${NC} Pushed to remote"
    fi
fi

echo ""
echo -e "${GREEN}Version bump complete!${NC}"
echo "Version: $CURRENT_VERSION → $NEW_VERSION"
