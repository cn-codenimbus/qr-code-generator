# QR-Code Vektor-Generator

Ein minimalistischer, aber mächtiger QR-Code-Generator in PHP, der echte Vektordateien (SVG/EPS) erstellt.

🌐 **Live Demo:** [qr-code-gen.codenimbus.de](https://qr-code-gen.codenimbus.de)

## Features

✨ **Vektor-Output**: Echte SVG- und EPS-Dateien (skalierbar ohne Qualitätsverlust)  
🎨 **Vollständig anpassbar**: Farben, Größe, Fehlerkorrektur, Ränder  
🖼️ **Logo-Integration**: Upload und automatische Skalierung von Logos  
📱 **Responsive Design**: Moderne, dunkle Benutzeroberfläche  
⚡ **Sofortiger Download**: Keine Registrierung oder Limits  
🎯 **Druck-optimiert**: EPS-Format für professionelle Druckanwendungen  

## Screenshots

Das Tool bietet eine intuitive Benutzeroberfläche mit allen wichtigen Einstellungen:
- Inhalt (Text/URL)
- Format (SVG/EPS)
- Fehlertoleranz (L/M/Q/H)
- Anpassbare Farben
- Logo-Upload mit Größensteuerung

## Installation

### Voraussetzungen
- PHP 8.0+ 
- Composer

### Setup
```bash
# Repository klonen
git clone https://github.com/[dein-username]/qr-code-generator.git
cd qr-code-generator

# Dependencies installieren
composer install

# Lokalen Server starten
php -S localhost:8000
```

Dann öffne [http://localhost:8000](http://localhost:8000) in deinem Browser.

## Verwendung

1. **Text/URL eingeben**: Der Inhalt, der im QR-Code kodiert werden soll
2. **Format wählen**: SVG (empfohlen) oder EPS für Druck
3. **Fehlertoleranz einstellen**: 
   - L = Niedrig (7% Fehlerkorrektur)
   - M = Mittel (15% Fehlerkorrektur) 
   - Q = Quartil (25% Fehlerkorrektur)
   - H = Hoch (30% Fehlerkorrektur, empfohlen für Logos)
4. **Optionale Anpassungen**: Farben, Größe, Ränder, Logo
5. **Generieren & Download**: Sofortiger Download der Vektordatei

## Technische Details

- **Backend**: PHP mit [endroid/qr-code](https://github.com/endroid/qr-code) Library
- **Frontend**: Vanilla HTML/CSS mit modernem dunklen Design
- **Output-Formate**: SVG (web-optimiert) und EPS (druck-optimiert)
- **Logo-Support**: PNG, JPG, WEBP, GIF mit automatischer Skalierung

## Deployment

Für die Produktionsumgebung:

1. Alle Dateien auf den Webserver hochladen
2. `composer install --no-dev` ausführen
3. Webserver auf `index.php` als Eingangsseite konfigurieren

### Apache .htaccess (optional)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## Lizenz

MIT License - siehe [LICENSE](LICENSE) Datei für Details.

## Beitragen

Pull Requests sind willkommen! Für größere Änderungen bitte zuerst ein Issue erstellen.

## Credits

- QR-Code-Generierung: [endroid/qr-code](https://github.com/endroid/qr-code)
- Entwickelt von [CodeNimbus](https://codenimbus.de)

---

**Live-Version:** [qr-code-gen.codenimbus.de](https://qr-code-gen.codenimbus.de) 