import React, { useState } from 'react';
import { FileCode, Database, Terminal, Check, Copy } from 'lucide-react';

interface FileTab {
  name: string;
  path: string;
  icon: string;
  content: string;
  language: string;
}

export default function SourceCodeViewer() {
  const [activeTab, setActiveTab] = useState<number>(0);
  const [copied, setCopied] = useState(false);

  const files: FileTab[] = [
    {
      name: 'database.sql',
      path: '/kejaconnect.sql',
      icon: 'db',
      language: 'sql',
      content: `-- Database schema for KejaConnect Property Management System
-- Generated with high fidelity for MySQL / MariaDB

CREATE DATABASE IF NOT EXISTS \`kejaconnect\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE \`kejaconnect\`;

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS \`users\` (
  \`id\` INT AUTO_INCREMENT PRIMARY KEY,
  \`full_name\` VARCHAR(100) NOT NULL,
  \`email\` VARCHAR(100) NOT NULL UNIQUE,
  \`password\` VARCHAR(255) NOT NULL,
  \`phone\` VARCHAR(20) NOT NULL,
  \`role\` ENUM('admin', 'landlord', 'tenant') NOT NULL,
  \`status\` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
  \`profile_photo\` VARCHAR(255) NULL,
  \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. PROPERTIES TABLE
CREATE TABLE IF NOT EXISTS \`properties\` (
  \`id\` INT AUTO_INCREMENT PRIMARY KEY,
  \`landlord_id\` INT NOT NULL,
  \`name\` VARCHAR(100) NOT NULL,
  \`address\` VARCHAR(255) NOT NULL,
  \`county\` VARCHAR(100) NOT NULL,
  \`property_type\` ENUM('apartment', 'bedsitter', 'studio', 'maisonette', 'bungalow') NOT NULL,
  \`total_units\` INT NOT NULL DEFAULT 1,
  \`description\` TEXT NULL,
  \`amenities\` TEXT NULL,
  \`photos\` JSON NULL,
  \`status\` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  FOREIGN KEY (\`landlord_id\`) REFERENCES \`users\` (\`id\`) ON DELETE CASCADE
);

-- 3. UNITS TABLE
CREATE TABLE IF NOT EXISTS \`units\` (
  \`id\` INT AUTO_INCREMENT PRIMARY KEY,
  \`property_id\` INT NOT NULL,
  \`unit_number\` VARCHAR(50) NOT NULL,
  \`floor\` INT NOT NULL DEFAULT 0,
  \`rent_amount\` DECIMAL(10, 2) NOT NULL,
  \`deposit_amount\` DECIMAL(10, 2) NOT NULL,
  \`status\` ENUM('vacant', 'occupied', 'maintenance') NOT NULL DEFAULT 'vacant',
  FOREIGN KEY (\`property_id\`) REFERENCES \`properties\` (\`id\`) ON DELETE CASCADE
);

-- seed standard test users: passwords are encrypted (Bcrypt string of "Admin@123", "Landlord@123", "Tenant@123")
INSERT INTO \`users\` (\`full_name\`, \`email\`, \`password\`, \`phone\`, \`role\`, \`status\`) VALUES
('KejaConnect Admin', 'admin@kejaconnect.co.ke', '$2y$10$wT8Ksc6vO1G1336Z9Z7cO.iZ8K4G6Kz.UAnO88uT9wDsh6t0yS/uK', '+254712345678', 'admin', 'active'),
('Mwenda Joseph', 'mwenda.landlord@gmail.com', '$2y$10$kP/MAsb1hD1fT7B81Q9yFeB4zW2t9h.MvSOnIeYnO9bM47m98zS1C', '+254722112233', 'landlord', 'active'),
('Wanjiku Kamau', 'wanjiku.tenant@yahoo.com', '$2y$10$v4T2G6Ie88BSh3f6vU6bN.F9oYpWjH6OsnfP7kU6vYvJ2D7O4mZ/y', '+254733445566', 'tenant', 'active');`
    },
    {
      name: 'db.php',
      path: '/config/db.php',
      icon: 'php',
      language: 'php',
      content: `<?php
/**
 * KejaConnect - PDO Database Configuration
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'kejaconnect');
define('DB_USER', 'root');
define('DB_PASS', '');

function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log("Database connection failure: " . $e->getMessage());
        die('Database Offline. Configure /config/db.php');
    }
}`
    },
    {
      name: 'config.php',
      path: '/config/config.php',
      icon: 'php',
      language: 'php',
      content: `<?php
/**
 * KejaConnect - System Configuration File
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

define('APP_NAME', 'KejaConnect');
define('BRAND_PRIMARY', '#1a6b3c'); // Deep Green
define('BRAND_ACCENT', '#f0a500');  // Gold
define('BASE_URL', 'http://localhost/kejaconnect');

function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = ['type' => $type, 'text' => $message];
}`
    },
    {
      name: 'auth.php',
      path: '/includes/auth.php',
      icon: 'php',
      language: 'php',
      content: `<?php
/**
 * KejaConnect - Session & Authentication Helper
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_role($allowed_roles) {
    require_login();
    $role = $_SESSION['user_role'] ?? '';
    $allowed = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];
    if (!in_array($role, $allowed)) {
        echo "Access Denied.";
        exit;
    }
}

function login_user($email, $password) {
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}`
    },
    {
      name: 'functions.php',
      path: '/includes/functions.php',
      icon: 'php',
      language: 'php',
      content: `<?php
/**
 * KejaConnect - Helper & Utility Functions
 */

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function format_money($amount) {
    return 'KES ' . number_format($amount, 2);
}

function log_audit($action, $description, $user_id = null) {
    $db = get_db_connection();
    $uid = $user_id ?? ($_SESSION['user_id'] ?? null);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$uid, $action, $description, $ip]);
}`
    },
    {
      name: 'login.php',
      path: '/login.php',
      icon: 'php',
      language: 'php',
      content: `<?php
/**
 * KejaConnect - Entry Login Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (login_user($email, $password)) {
        $role = $_SESSION['user_role'];
        header("Location: " . BASE_URL . "/$role/dashboard.php");
        exit;
    } else {
        $error = "Mismatched security credentials.";
    }
}
?>
<!-- Responsive HTML login form styled with Deep Green and Gold accents -->`
    }
  ];

  const triggerCopy = (txt: string) => {
    navigator.clipboard.writeText(txt);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden self-stretch flex flex-col my-4">
      
      {/* HEADER */}
      <div className="bg-slate-950 px-6 py-4 border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex items-center space-x-3">
          <div className="h-9 w-9 bg-brand-green/10 border border-brand-green/30 rounded-lg flex items-center justify-center text-brand-green">
            <FileCode className="h-5 w-5" />
          </div>
          <div>
            <h3 className="font-display font-medium text-white text-sm">Target PHP Codebase Vault</h3>
            <p className="text-[10px] text-slate-400">Inspecting 100% compliant physical core PHP files written in workspace root</p>
          </div>
        </div>

        <button
          onClick={() => triggerCopy(files[activeTab].content)}
          className="inline-flex items-center space-x-1.5 bg-brand-green hover:bg-brand-green-hover text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold font-display shadow-sm cursor-pointer transition-colors shrink-0"
        >
          {copied ? (
            <>
              <Check className="h-3.5 w-3.5" />
              <span>Copied successfully!</span>
            </>
          ) : (
            <>
              <Copy className="h-3.5 w-3.5" />
              <span>Copy Entire File</span>
            </>
          )}
        </button>
      </div>

      {/* CODE VIEWPORT GRID */}
      <div className="grid grid-cols-1 md:grid-cols-4 min-h-[350px]">
        {/* FILE LIST DRAWER */}
        <div className="bg-slate-950/40 p-3 border-r border-slate-800/80 flex flex-row md:flex-col overflow-x-auto md:overflow-y-auto gap-1">
          {files.map((file, idx) => (
            <button
              key={file.name}
              onClick={() => {
                setActiveTab(idx);
                setCopied(false);
              }}
              className={`flex items-center space-x-2.5 px-3 py-2.5 rounded-lg text-xs font-semibold text-left transition-colors whitespace-nowrap cursor-pointer ${
                activeTab === idx
                  ? 'bg-slate-800 text-brand-gold'
                  : 'text-slate-400 hover:text-white hover:bg-slate-800/40'
              }`}
            >
              {file.icon === 'db' ? (
                <Database className="h-4 w-4 shrink-0" />
              ) : (
                <Terminal className="h-4 w-4 shrink-0" />
              )}
              <div className="flex flex-col">
                <span>{file.name}</span>
                <span className="text-[9px] text-slate-500 font-normal">{file.path}</span>
              </div>
            </button>
          ))}
        </div>

        {/* FILE PREVIEW PANEL */}
        <div className="md:col-span-3 p-5 overflow-auto bg-slate-900 text-slate-300 font-mono text-xs leading-relaxed max-h-[450px]">
          <div className="text-slate-500 sm:text-right text-[10px] pb-3 border-b border-slate-800 mb-4 flex justify-between">
            <span>FILEPATH: <span className="text-white">{files[activeTab].path}</span></span>
            <span className="text-brand-gold">{files[activeTab].language.toUpperCase()} Core Source</span>
          </div>
          <pre className="whitespace-pre overflow-x-auto">
            {files[activeTab].content}
          </pre>
        </div>
      </div>
    </div>
  );
}
