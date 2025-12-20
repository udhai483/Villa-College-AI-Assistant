# GitHub Submission Guide

This guide will help you push your project to a private GitHub repository and add the instructor as a collaborator.

## Prerequisites

- Git installed on your computer
- GitHub account created
- Project completed and tested

## Step 1: Create a Private GitHub Repository

1. Go to [GitHub](https://github.com) and log in
2. Click the **+** icon in the top-right corner
3. Select **New repository**
4. Configure the repository:
   - **Repository name**: `villa-college-ai-assistant` (or your preferred name)
   - **Description**: "AI-powered chatbot for Villa College with RAG implementation"
   - **Visibility**: Select **Private** (IMPORTANT!)
   - **DO NOT** initialize with README, .gitignore, or license (we already have these)
5. Click **Create repository**

## Step 2: Prepare Your Local Project

Open PowerShell in your project directory (`d:\Assignment`) and run:

```powershell
# Initialize Git repository (if not already done)
git init

# Check Git status
git status
```

## Step 3: Create .gitignore (if not exists)

Ensure your `.gitignore` file includes:

```
/node_modules
/public/hot
/public/storage
/public/build
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
.DS_Store
Thumbs.db
```

This ensures sensitive files (like `.env` with API keys) are not pushed to GitHub.

## Step 4: Add Files to Git

```powershell
# Add all files to staging
git add .

# Create initial commit
git commit -m "Initial commit: Villa College AI Assistant with RAG implementation"
```

## Step 5: Connect to GitHub Repository

Replace `YOUR_USERNAME` with your actual GitHub username:

```powershell
# Add GitHub repository as remote
git remote add origin https://github.com/YOUR_USERNAME/villa-college-ai-assistant.git

# Verify remote was added
git remote -v
```

## Step 6: Push to GitHub

```powershell
# Push to GitHub (main branch)
git push -u origin main
```

If you get an error about `master` vs `main`:

```powershell
# Rename branch to main
git branch -M main

# Push again
git push -u origin main
```

## Step 7: Add Instructor as Collaborator

1. Go to your repository on GitHub
2. Click **Settings** (top tab)
3. Click **Collaborators** (left sidebar)
4. Click **Add people** (green button)
5. Enter: `ahmed.ashham@villacollege.edu.mv`
6. Click **Add ahmed.ashham@villacollege.edu.mv to this repository**
7. Select role: **Write** or **Admin** (recommended: **Write**)
8. Click **Add [name] to villa-college-ai-assistant**

The instructor will receive an email invitation and will be able to access your private repository.

## Step 8: Verify Submission

1. **Check Repository Visibility**: Ensure the repository shows a 🔒 **Private** badge
2. **Verify Collaborator**: Go to Settings → Collaborators and confirm the instructor is listed
3. **Check Files**: Browse your repository and ensure all files are present
4. **Verify .env is NOT there**: Confirm that `.env` file is NOT visible (it should be in `.gitignore`)
5. **Test README**: Open the README.md and verify all instructions are clear

## Step 9: Test Instructions

Before final submission, test that your instructions work:

1. **Clone to a new directory** (to simulate fresh setup):
   ```powershell
   cd ..
   git clone https://github.com/YOUR_USERNAME/villa-college-ai-assistant.git test-submission
   cd test-submission
   ```

2. **Follow your own README instructions**:
   - Create `.env` from `.env.example`
   - Add your API keys
   - Run `docker-compose up -d`
   - Test the application at http://localhost:8080

3. **If everything works**, your submission is ready!

## Troubleshooting

### Authentication Issues

If GitHub asks for credentials:

**Option 1: Personal Access Token (Recommended)**
1. Go to GitHub Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate new token
3. Select scopes: `repo` (all), `workflow`
4. Copy the token
5. Use token as password when Git asks

**Option 2: GitHub CLI**
```powershell
# Install GitHub CLI
winget install GitHub.cli

# Authenticate
gh auth login
```

### Push Rejected

If push is rejected:

```powershell
# Pull first (if repository has initial files)
git pull origin main --allow-unrelated-histories

# Then push
git push -u origin main
```

### Large Files Warning

If you get warnings about large files:

```powershell
# Remove node_modules and vendor if accidentally added
git rm -r --cached node_modules
git rm -r --cached vendor
git commit -m "Remove vendor and node_modules"
git push
```

## Making Updates After Initial Push

If you need to update your repository:

```powershell
# Check what changed
git status

# Add changes
git add .

# Commit with message
git commit -m "Description of changes"

# Push to GitHub
git push
```

## Final Checklist

Before notifying your instructor:

- [ ] Repository is **Private**
- [ ] Instructor (ahmed.ashham@villacollege.edu.mv) added as collaborator
- [ ] README.md contains clear setup instructions
- [ ] `.env.example` has placeholders (no real API keys)
- [ ] `.env` is in `.gitignore` and NOT pushed
- [ ] All code is committed and pushed
- [ ] Self-Assessment section is complete
- [ ] Tested that `docker-compose up -d` works
- [ ] Tested that `php artisan scrape:villacollege` command exists
- [ ] Application accessible at http://localhost:8080

## Repository URL Format

Your final repository URL will be:
```
https://github.com/YOUR_USERNAME/villa-college-ai-assistant
```

Share this URL with your instructor (though they will already have access as a collaborator).

---

**Good luck with your submission! 🚀**
