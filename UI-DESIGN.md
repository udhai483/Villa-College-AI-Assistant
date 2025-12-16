# UI Design Documentation

## 🎨 Design System

### Color Palette
- **Primary Blue**: #0ea5e9 (Tailwind primary-600)
- **Background**: #f9fafb (gray-50)
- **White**: #ffffff
- **Text**: #111827 (gray-900)
- **Secondary Text**: #6b7280 (gray-500)

### Typography
- **Font Family**: Inter (from Google Fonts)
- **Headings**: Bold, 700 weight
- **Body**: Regular, 400 weight
- **Small Text**: 12-14px

### Components
- **Buttons**: Rounded (rounded-lg), shadowed, smooth transitions
- **Cards**: White background, shadow-lg, rounded-xl
- **Inputs**: Border with focus ring, rounded-lg

---

## 📱 Login Screen UI

### Layout
```
┌─────────────────────────────────────────┐
│                                         │
│           [Villa Logo Icon]             │
│                                         │
│      Villa College AI Assistant         │
│   Your intelligent chatbot powered by   │
│          RAG technology                 │
│                                         │
│  ┌───────────────────────────────────┐  │
│  │                                   │  │
│  │    [Error Message if any]         │  │
│  │                                   │  │
│  │      Welcome Back!                │  │
│  │  Sign in with your Villa College  │  │
│  │           account                 │  │
│  │                                   │  │
│  │  ┌─────────────────────────────┐  │  │
│  │  │  [G] Sign in with Google    │  │  │
│  │  └─────────────────────────────┘  │  │
│  │                                   │  │
│  │  ┌─────────────────────────────┐  │  │
│  │  │  ℹ️ Authorized Domains Only  │  │  │
│  │  │  Only @villacollege.edu.mv  │  │  │
│  │  │  and @students emails        │  │  │
│  │  └─────────────────────────────┘  │  │
│  │                                   │  │
│  └───────────────────────────────────┘  │
│                                         │
│     © 2025 Villa College               │
│   Powered by AI & RAG Technology        │
│                                         │
└─────────────────────────────────────────┘
```

### Key Features
- **Gradient Background**: Soft blue gradient (from-primary-50 to-primary-100)
- **Centered Layout**: Vertically and horizontally centered
- **Modern Card**: Clean white card with shadow
- **Google Button**: Custom styled with Google logo
- **Info Box**: Blue background notice for domain restriction
- **Responsive**: Works on mobile, tablet, and desktop
- **Error Handling**: Red alert box for authentication errors

### Visual Elements
1. **Logo Icon**: Blue rounded square with chat bubble icon
2. **Title**: Large, bold heading
3. **Subtitle**: Smaller gray text
4. **Card**: Elevated white container
5. **Button**: Full-width with Google colors
6. **Notice**: Light blue information box
7. **Footer**: Small copyright text

---

## 💬 Chatbot Screen UI

### Layout
```
┌─────────────────────────────────────────────────────────┐
│  [Logo] Villa College AI Assistant    [User] [Logout]  │
│         Powered by RAG Technology                       │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │  [AI] Thank you for your question...            │   │
│  │       10:30 AM                                  │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
│                   ┌──────────────────────────────────┐  │
│                   │ How do I apply for admission?   │  │
│                   │              10:31 AM       [👤]│  │
│                   └──────────────────────────────────┘  │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │  [AI] To apply for admission at Villa College...│   │
│  │       10:31 AM                                  │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────┐  ┌─────────┐   │
│  │ Ask me anything about Villa...     │  │  Send   │   │
│  └────────────────────────────────────┘  └─────────┘   │
│  Press Enter to send, Shift+Enter for new line         │
└─────────────────────────────────────────────────────────┘
```

### Key Features
1. **Header Bar**
   - Villa College logo and branding
   - User info (name, email, avatar)
   - Logout button
   - Fixed at top
   - White background with subtle shadow

2. **Chat Area**
   - Scrollable message container
   - Gray background (bg-gray-50)
   - Auto-scroll to latest message
   - Maximum width container (max-w-4xl)

3. **Message Bubbles**
   - **AI Messages**: 
     - Left-aligned
     - White background with border
     - AI avatar (blue circle with lightbulb icon)
     - Black text
   - **User Messages**:
     - Right-aligned
     - Blue background (primary-600)
     - White text
     - User's Google avatar
   - Timestamps below each message
   - Rounded corners (rounded-2xl)

4. **Welcome State** (No messages)
   - Centered welcome message
   - Large icon
   - Quick action buttons
   - Suggested questions

5. **Loading Indicator**
   - Three animated dots
   - Shows while AI is "thinking"

6. **Input Area**
   - Fixed at bottom
   - White background
   - Auto-expanding textarea
   - Send button (blue)
   - Keyboard shortcut hint

### Responsive Design
- **Desktop**: Full width, side margins
- **Tablet**: Adjusted padding
- **Mobile**: Full screen, optimized touch targets

### Interactions
- **Send Message**: Click button or press Enter
- **New Line**: Shift + Enter
- **Scroll**: Auto-scroll on new message
- **Quick Actions**: Click suggestion to populate input

### Color Coding
- **AI Messages**: `bg-white` with `border-gray-200`
- **User Messages**: `bg-primary-600` (blue)
- **Timestamps**: `text-gray-500`
- **Input**: `border-gray-300` with `focus:ring-primary-500`
- **Send Button**: `bg-primary-600 hover:bg-primary-700`

---

## 🎯 UI Best Practices Implemented

### 1. **Modern & Clean**
- Minimalist design
- Ample white space
- Subtle shadows
- Smooth transitions

### 2. **Professional**
- Consistent branding
- Corporate color scheme
- Professional typography
- Clear hierarchy

### 3. **Simple & Intuitive**
- Clear call-to-actions
- Self-explanatory UI
- Minimal learning curve
- Familiar patterns

### 4. **Accessible**
- High contrast text
- Clear focus states
- Keyboard navigation
- Screen reader friendly

### 5. **Responsive**
- Mobile-first approach
- Flexible layouts
- Touch-friendly targets
- Adaptive spacing

### 6. **User-Friendly**
- Helpful error messages
- Loading indicators
- Visual feedback
- Clear navigation

---

## 🔄 State Management

### Login Screen States
1. **Default**: Clean login form
2. **Error**: Red alert with message
3. **Loading**: (Google OAuth redirect)

### Chat Screen States
1. **Empty**: Welcome message with suggestions
2. **Messages**: Conversation history
3. **Loading**: Typing indicator
4. **Error**: (Handled in message)

---

## 📐 Spacing System

- **xs**: 0.25rem (4px)
- **sm**: 0.5rem (8px)
- **md**: 1rem (16px)
- **lg**: 1.5rem (24px)
- **xl**: 2rem (32px)

---

## 🎨 Component Examples

### Button Classes
```css
.btn-primary {
  @apply bg-primary-600 hover:bg-primary-700 
         text-white font-semibold py-3 px-6 
         rounded-lg transition duration-200 
         shadow-md hover:shadow-lg;
}
```

### Card Classes
```css
.card {
  @apply bg-white rounded-xl shadow-lg overflow-hidden;
}
```

### Input Classes
```css
.input-field {
  @apply w-full px-4 py-3 rounded-lg 
         border border-gray-300 
         focus:ring-2 focus:ring-primary-500 
         focus:border-transparent 
         transition duration-200;
}
```
