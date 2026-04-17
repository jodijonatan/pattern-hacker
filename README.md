# 🏆 Pattern Hacker - LKS 2026 Winner

**Pattern Hacker** is a high-performance, accessible memory game developed for the **Medan City Level Web Technologies LKS (Lomba Kompetensi Siswa) Competition**. This project secured **1st Place (Winner)** by demonstrating excellence in modern web architecture, accessibility, and secure system design.

---

## 🚀 Overview

Pattern Hacker challenges players to memorize and replicate complex patterns, testing their cognitive speed and memory. Beyond gameplay, the project serves as a showcase for modular full-stack development, integrating a reactive frontend with a robust, custom-built PHP backend.

### ✨ Key Features

- **Dynamic Pattern Mechanics**: Procedurally generated memory patterns that scale in difficulty.
- **Accessibility First**: A dedicated **Accessibility Tool** allowing users to switch themes, scale font sizes, and toggle dyslexia-friendly typography.
- **Secure Authentication**: Session-based user management with prepared SQL statements to prevent common vulnerabilities.
- **Real-time Leaderboard**: Competitive scoring system tracking top hackers across the competition.
- **Responsive & Premium UI**: Built with Tailwind CSS 4 for a sleek, modern, and high-performance design.

---

## 🛠️ Tech Stack

### Frontend
- **Framework**: [React 19](https://react.dev/) (Latest)
- **Tooling**: [Vite 6](https://vitejs.dev/)
- **Styling**: [Tailwind CSS 4](https://tailwindcss.com/)
- **State Management**: [Zustand 5](https://github.com/pmndrs/zustand)
- **Routing**: [React Router 7](https://reactrouter.com/)
- **Icons**: [Lucide React](https://lucide.dev/)

### Backend
- **Core**: Native PHP (Modular PSR-4 Architecture)
- **Routing**: Custom Pattern-based Router
- **Database**: MySQL with Custom SchemaBuilder Migration Engine
- **Auth**: Session-based Authentication

---

## 📂 Project Structure

```text
├── public/             # Static assets
├── server/             # PHP Backend (REST API)
│   ├── Controllers/    # Request Handling
│   ├── Core/           # Database & Engine Utilities
│   ├── migrate.php     # Schema Migration Tool
│   └── index.php       # API Entry Point
├── src/                # React Frontend
│   ├── components/     # UI & Page Components
│   ├── store/          # Zustand State Models
│   └── main.jsx        # App Entry Point
└── vite.config.js      # Build Configuration
```

---

## ⚙️ Installation & Setup

### Prerequisites
- **Node.js**: v18+
- **PHP**: v8.1+
- **MySQL**: v5.7+ or v8.0+

### 1. Frontend Setup
```bash
# Install dependencies
npm install

# Start development server
npm run dev
```

### 2. Backend Setup
1. Configure your database in `server/config.php`.
2. Run the migration to set up tables:
   - Navigate to `http://localhost/path-to-project/server/migrate.php` in your browser.
   - Or run `php server/migrate.php` via CLI if supported by your setup.

### 3. Environment
Ensure your frontend `src/config.js` points to the correct API base URL.

---

## 🏅 Achievement
- **Competition**: Lomba Kompetensi Siswa (LKS) SMK
- **Field**: Web Technologies
- **Level**: City Level (Medan)
- **Result**: 🥇 1st Place

---

## 👤 Credits
Developed by **Jodi Jonatan**.

> [!NOTE]
> This project was developed under a strictly timed competition environment, focusing on clean code, scalability, and exceptional user experience.
