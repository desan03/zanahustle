<?php
/**
 * Security Headers Configuration
 * Sets comprehensive HTTP security headers to protect against common attacks
 */

// Prevent clickjacking attacks
header('X-Frame-Options: SAMEORIGIN');

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Enforce HTTPS
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');

// XSS Protection
header('X-XSS-Protection: 1; mode=block');

// Referrer Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Content Security Policy
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self';");

// Permissions Policy
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Additional security headers
header('X-Permitted-Cross-Domain-Policies: none');
header('Cross-Origin-Embedder-Policy: require-corp');
