# Pre-Deployment Checklist

Complete these tasks **before** running the deployment script.

---

## ✅ Server Requirements

### **1. Server Specifications**
- [ ] Ubuntu 20.04/22.04 or Debian 11/12
- [ ] Minimum: 2 CPU cores, 4GB RAM, 20GB disk
- [ ] Recommended: 4 CPU cores, 8GB RAM, 50GB disk
- [ ] Root or sudo access

**Cloud Provider Options:**
- DigitalOcean: $20/month Droplet (4GB RAM)
- AWS EC2: t3.medium instance
- Google Cloud: e2-medium instance
- Linode: 4GB Shared CPU
- Vultr: 4GB instance

### **2. Network Access**
- [ ] Port 22 open (SSH)
- [ ] Port 80 open (HTTP)
- [ ] Port 443 open (HTTPS)
- [ ] Static public IP address assigned
- [ ] SSH key authentication configured

---

## 🌐 DNS Configuration

### **Setup Domain DNS Record**

**Domain:** chat.villacollege.edu.mv

**DNS Settings:**
```
Type: A
Name: chat (or @ if using subdomain)
Value: YOUR_SERVER_IP_ADDRESS
TTL: 3600 (or Auto)
```

**Steps:**
1. Login to your DNS provider (e.g., Namecheap, Cloudflare, Google Domains)
2. Find DNS management for villacollege.edu.mv
3. Add A record pointing to your server IP
4. Wait 5-30 minutes for DNS propagation

**Verify DNS is working:**
```bash
# From your computer
nslookup chat.villacollege.edu.mv

# Or
dig chat.villacollege.edu.mv +short
```

Should return your server IP address.

---

## 🔐 Google OAuth Setup

### **Create OAuth 2.0 Credentials**

1. Go to: https://console.cloud.google.com/apis/credentials
2. Select or create a project: "Villa College AI Assistant"
3. Click "Create Credentials" → "OAuth client ID"
4. Application type: "Web application"
5. Name: "Villa College Chat Production"

**Authorized JavaScript origins:**
```
https://chat.villacollege.edu.mv
```

**Authorized redirect URIs:**
```
https://chat.villacollege.edu.mv/auth/google/callback
```

6. Click "Create"
7. **Copy Client ID and Client Secret** (you'll need these for .env)

### **Optional: Restrict to Organization**

Under "OAuth consent screen":
- User type: Internal (if using Google Workspace)
- Or configure domain restriction in app

---

## 🔑 OpenAI API Key (Optional)

If you want semantic search with GPT responses:

1. Go to: https://platform.openai.com/api-keys
2. Click "Create new secret key"
3. Name: "Villa College Production"
4. Copy the key (starts with sk-proj-...)
5. Add $10-20 credits at: https://platform.openai.com/settings/organization/billing/overview

**Cost Estimate:**
- Embeddings: ~$0.002 for all 95 entries
- Queries: ~$0.0002 per question
- $10 = ~50,000 questions

**Note:** App works perfectly without this using keyword search!

---

## 📝 Credentials Preparation

**Create this file locally to reference during deployment:**

```bash
# Save as: villa-credentials.txt

# Server Access
SERVER_IP=___.___.___.___ (your server IP)
SSH_USER=ubuntu (or root)
SSH_KEY=/path/to/your/key.pem

# Application
DOMAIN=chat.villacollege.edu.mv
DB_PASSWORD=__________________ (generate strong password)

# Google OAuth
GOOGLE_CLIENT_ID=____________________
GOOGLE_CLIENT_SECRET=____________________

# OpenAI (optional)
OPENAI_API_KEY=sk-proj-____________________
```

**Generate Strong DB Password:**
```bash
# On your computer
openssl rand -base64 32
```

---

## 🚀 Server Access Test

**Before proceeding, verify you can access your server:**

```bash
# Test SSH connection
ssh -i /path/to/key.pem ubuntu@YOUR_SERVER_IP

# If successful, you should see server prompt
```

**If SSH fails:**
- Check security group/firewall rules (port 22 open)
- Verify SSH key is correct
- Try password authentication if enabled
- Contact hosting provider support

---

## 📦 What the Deployment Will Do

The `deploy.sh` script will automatically:

1. ✅ Install Docker & Docker Compose
2. ✅ Install Certbot for SSL certificates
3. ✅ Clone GitHub repository to `/var/www/villa-ai`
4. ✅ Create `.env` from template (you'll edit credentials)
5. ✅ Generate Let's Encrypt SSL certificate
6. ✅ Start Docker containers (PHP, Nginx, MySQL)
7. ✅ Generate Laravel app key
8. ✅ Run database migrations
9. ✅ Optimize for production (cache config, routes, views)
10. ✅ Populate knowledge base (optional)
11. ✅ Configure firewall (UFW)

**Estimated Time:** 15-30 minutes

---

## 🎯 Deployment Commands

### **Step 1: Connect to Server**

```bash
# From your local machine
ssh -i /path/to/key.pem ubuntu@YOUR_SERVER_IP
```

### **Step 2: Download Deployment Script**

```bash
# On the server
curl -O https://raw.githubusercontent.com/udhai483/Villa-College-AI-Assistant/main/deploy.sh
chmod +x deploy.sh
```

### **Step 3: Run Deployment**

```bash
bash deploy.sh
```

**During deployment, you'll be prompted to:**
- Confirm prerequisites
- Edit `.env` with credentials (use villa-credentials.txt)
- Confirm SSL certificate generation
- Choose whether to populate knowledge base now

### **Step 4: Verify Deployment**

```bash
# Check containers are running
docker compose ps

# Test health endpoint
curl http://localhost:8080/api/health

# Check application logs
docker compose logs -f app
```

### **Step 5: Test Application**

**In your browser:**
1. Visit: https://chat.villacollege.edu.mv
2. Click "Sign in with Google"
3. Login with @villacollege.edu.mv email
4. Ask a test question
5. Verify response and sources

---

## ⚠️ Common Issues & Solutions

### **DNS Not Propagating**
```bash
# Check DNS status
dig chat.villacollege.edu.mv +short

# If not showing your IP, wait 30 minutes and try again
# Or use: https://dnschecker.org
```

### **SSL Certificate Fails**
```bash
# Make sure DNS is pointing to server first
# Check port 80 is accessible
sudo ufw allow 80/tcp

# Try manual certificate
sudo certbot certonly --standalone -d chat.villacollege.edu.mv
```

### **Port 8080 Required**
If SSL setup failed, access via:
```
http://YOUR_SERVER_IP:8080
```

### **Docker Permission Denied**
```bash
# Add user to docker group
sudo usermod -aG docker $USER

# Logout and login again
exit
# ssh back in
```

---

## 📋 Post-Deployment Tasks

After successful deployment:

- [ ] Test login with @villacollege.edu.mv email
- [ ] Test chat with sample questions
- [ ] Verify health endpoint: `/api/health`
- [ ] Check logs for errors
- [ ] Setup automated backups (see PRODUCTION-DEPLOYMENT.md)
- [ ] Configure log rotation
- [ ] Setup monitoring alerts
- [ ] Add to uptime monitoring service
- [ ] Document production credentials securely
- [ ] Train staff on using the chatbot
- [ ] Announce to Villa College community

---

## 🆘 Need Help?

**Issues:** https://github.com/udhai483/Villa-College-AI-Assistant/issues

**Documentation:**
- [PRODUCTION-DEPLOYMENT.md](PRODUCTION-DEPLOYMENT.md) - Full deployment guide
- [MONITORING-GUIDE.md](MONITORING-GUIDE.md) - Monitoring & metrics
- [SECURITY-STATUS.md](SECURITY-STATUS.md) - Security verification

---

**Ready to deploy? Complete this checklist, then run `bash deploy.sh` on your server!** 🚀
