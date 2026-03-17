Shopware 1.17.2
---

### de
* _Hinzugefügt_ Fehlerbehebung für Giropay-Fehler beim Aktualisieren.

### en
* _Added_ Fix for Giropay error when trying to update.

Shopware 1.17.1
---

### de

* _Entfernt_ Giropay ist eine veraltete Zahlungsmethode

### en

* _Removed_ Giropay deprecated payment method

Shopware 1.17.0
---

### de

* _HINZUGEFÜGT_ - Kompatibilität mit Shopware 6.7

### en

* _ADDED_ - Compatibility with Shopware 6.7

Shopware 1.16.3
---

### de

* _BEHOBEN_ - Fehler „Keine Kreditkarte ausgewählt“, wenn stripe.js nicht auf die Seite geladen werden konnte
* _BEHOBEN_ - Fehler „Keine Kreditkarte ausgewählt“, wenn das Formular programmgesteuert übermittelt wird

### en

* _FIXED_ - No credit card selected error when stripe.js failed to load into page
* _FIXED_ - No credit card selected error when form is submitted programmatically

Shopware 1.16.1
---

### de

* _BEHOBEN_ - Überprüfung der Anmeldeinformationen, Webhook-Registrierung und Abrufen des Landes vom Stripe-Konto auf der Plugin-Einstellungsseite von Shopware 6.6

### en

* _FIXED_ - Credential verification, webhook registration and country fetching from Stripe account, in plugin settings page on Shopware 6.6


Shopware 1.15.2
---

### de

* _BEHOBEN_ - Undefinierter Array-Schlüssel „Karte“ behoben

### en

* _FIXED_ - Fixed Undefined array key "card"

Shopware 1.15.0
---

### de

* _BEHOBEN_ - Entfernen Sie den Anweisungsdeskriptorparameter

### en

* _FIXED_ - Remove statement descriptor parameter

Shopware 1.14.0
---

### de

* _BEHOBEN_ - Fehler „Keine Kreditkarte ausgewählt“ behoben

### en

* _FIXED_ - Fixed No credit card selected error

Shopware 1.13.0
---

### de

* _BEHOBEN_ - Entfernen Sie die Verwendung des Anforderungsstapels aus dem Konstruktorkörper
* _BEHOBEN_ - Entfernt die Zahlungsmethode Sofort

### en

* _FIXED_ - Remove the use of the request stack from the constructor body
* _FIXED_ - Remove Sofort payment method

Shopware 1.12.0
---

### de

* _BEHOBEN_ - Wallet-Zahlungsmethoden wie Apple Pay und Google Pay funktionieren jetzt.

### en

* _FIXED_ - The wallets payments methods as Apple Pay and Google Pay are now working.

Shopware 1.11.0
---

### de

* _HINZUGEFÜGT_ - Kompatibilität mit Shopware 6.5.
* _BEHOBEN_ - Verwenden Sie die PaymentIntents-API anstelle der veralteten Sources-API für Klarna und Sofort.

### en

* _ADDED_ - Compatibility with Shopware 6.5
* _FIXED_ - Use PaymentIntents API instead of deprecated Sources API for Klarna and Sofort


Shopware 1.10.0
---

### de

* _HINZUGEFÜGT_ - Kompatibilität mit Shopware 6.5

### en

* _ADDED_ - The plugin now supports PHP version 8.1


Shopware 1.9.1
---

### de

* _HINZUGEFÜGT_ - Das Plugin benötigt nun mindestens Shopware Version 6.4.5.0
* _BEHOBEN_ - Entfernt die Möglichkeit die Pluginkonfiguration über die Seite "Meine Erweiterungen" aufzurufen. Die Pluginkonfiguration kann weiterhin über "Einstellungen > Erweiterungen" erreicht werden

### en

* _ADDED_ - The bundle now requires at least Shopware version 6.4.5.0
* _FIXED_ - Removes the possibility to access the plugin configuration from the "My extensions" page. The plugin configuration can still be reached via "Settings > Extensions"


Shopware 1.9.0
---

### de

* _HINZUGEFÜGT_ - Icons für Zahlungsanbieter werden nun nur bei Bedarf geladen
* _BEHOBEN_ - Innerhalb der Klarna Bestellreferenz wird nun zuerst die Bestellnummer genannt, dann der Shopname

### en

* _ADDED_ - Icons for payment providers are now only loaded when needed
* _FIXED_ - In the Klarna order reference the order number is now placed before the shop name

## 1.8.1

### de

**Neue Funktionen und Verbesserungen:**

* Erleichtert das Erweitern der Konfigurationsseite für Stripe Zahlungsmethoden.

### en

**New features and improvements:**

* Improves the extendability for config pages of stripe payment methods.


## 1.8.0

### de

**Neue Funktionen und Verbesserungen:**

* Die Stripe Dashboard Ansicht für eine bestimmte Zahlung kann nun direkt aus der Detailansicht der Bestellung für diese Zahlung erreicht werden.
* Die Zahlungsarten von Stripe werden nach fehlgeschlagener Zahlung wieder korrekt angezeigt.

### en

**New features and improvements:**

* The Stripe dashboard view for a specific payment can now be accessed directly from the order detail page for that payment.
* After a failed payment, the payment methods from stripe will be displayed correctly again.


## 1.7.2

### de

**Fehlerbehebungen:**

* Nettobestellungen werden nun korrekt an Klarna übergeben.

### en

**Bug fixes:**

* Orders with net prices are now transmitted correctly to Klarna.


## 1.7.1

### de

**Fehlerbehebungen:**

* Das Plugin lässt sich jetzt wieder ohne Fehler installieren.

### en

**Bug fixes:**

* The plugin can now be installed again without errors.


## 1.7.0

### de

**Neue Funktionen und Verbesserungen:**

* Stellt die Kompatibilität mit Shopware 6.4.0.0 her.

### en

**New features and improvements:**

* Establishes compatibility with Shopware 6.4.0.0.


## 1.6.2

### de

**Fehlerbehebungen:**

* Zahlungen mit Digital Wallets (Apple Pay, Google Pay, ...) funktionieren nun wieder, wenn sich das Land des Kunden
  und das des hinterlegten Stripe-Accounts unterscheiden.
  * Dazu wird nun in der Konfiguration des Plugins das Land des Stripe-Accounts hinterlegt. Standardmäßig wird das Land
  des Standard-Sales-Channels verwendet.

### en

**Bug fixes:**

* Payments with digital wallets (Apple Pay, Google Pay, ...) now work again if the country of the customer and the
  country of the configured Stripe account differ.
  * For this purpose, the country of the Stripe account is now stored in the configuration of the plugin. By default the
  country of the default sales channel is used.


## 1.6.1

### de

**Fehlerbehebungen:**

* Zahlungen mit Klarna funktionieren nun auch mit Bundle-Produkten.

### en

**Bug fixes:**

* Payments with Klarna now also work with bundle products.


## 1.6.0

### de

**Neue Funktionen und Verbesserungen:**

* Das Plugin loggt nun Fehler, die beim Bezahlvorgang auftreten.

**Fehlerbehebungen:**

* Der Zahlungsstatus von Bestellungen wird nun korrekt auf "Fehlgeschlagen" gesetzt, wenn eine Zahlung fehlgeschlagen ist. Zuvor wurde der Zahlungsstatus gegebenenfalls auf "Abgebrochen" gesetzt.

### en

**New features and improvements:**

* The plugin now logs errors which occur during the payment process.

**Bug fixes:**

* The payment status of orders is now correctly set to "Failed" when a payment has failed. Previously the payment status was possibly set to "Cancelled".


## 1.5.0

### de

**Neue Funktionen und Verbesserungen:**

* Das Plugin unterstützt nun Shopware Version 6.3.2.0.

### en

**New features and improvements:**

* The plugin now supports shopware version 6.3.2.0.


## 1.4.0

### de

**Neue Funktionen und Verbesserungen:**

* Das Plugin unterstützt jetzt die Zahlungsarten Giropay, iDeal, P24, EPS und Bancontact.
* Das Plugin unterstützt nun Shopware Version 6.3.1.

**Anforderungen:**

* Das Plugin benötigt nun mindestens Shopware Version 6.3.1.0.

### en

**New features and improvements:**

* The plugin now supports the payment methods Giropay, iDeal, P24, EPS and Bancontact.
* The plugin now supports shopware version 6.3.1.

**Requirements:**

* The plugin now requires at least Shopware version 6.3.1.0.


## 1.3.0

### de

**Neue Funktionen und Verbesserungen:**

* Verbessert den Titel des Browsertabs der Pluginkonfiguration.
* Das Plugin unterstützt nun Shopware Version 6.3.0 (ab Version 6.3.0.2).

**Fehlerbehebungen:**

* Die Validierung der Zugangsdaten in der Plugin-Konfiguration für einen speziellen Sales-Channel funktioniert nun, auch wenn sie nicht explizit für diesen Sales-Channel hinterlegt wurden.

**Voraussetzungen:**

* Das Plugin benötigt nun mindestens Shopware Version 6.3.0.2.

### en

**New features and improvements:**

* Improves the browser tab title of the plugin configuration.
* The plugin now supports Shopware version 6.3.0 (from version 6.3.0.2).

**Bug fixes:**

* The validation of credentials in the plugin configuration for a specific sales channel now works, even if they were not explicitly defined for this sales channel.

**Requirements:**

* The plugin now requires at least Shopware version 6.3.0.2.


## 1.2.2

### de

**Verbesserungen:**

* Die Stabilität der Zahlungsabwicklung mit Stripe wurde verbessert.

**Fehlerbehebungen:**

* Die Installation des Plugins ist nun auch auf System möglich, welche eine andere Sprache als Deutsch oder Englisch als Standard festgelegt haben.
* Webhooks, die erfolgreich verarbeitet wurden, werden im Stripe Dashboard nun auch als _Erfolgreich_ angezeigt.

### en

**Improvements:**

* Improved stability of payment processing with Stripe.

**Bug fixes:**

* Installation of the plugin is now possible on systems that have a language other than German or English set as default.
* Webhooks that were processed successfully are now also displayed as successful in the Stripe Dashboard.


## 1.2.1

### de

**Fehlerbehebungen:**

* Behebt einen Fehler, der dazu führte, dass die Frontend Routen-Generierung bei URLs mit Pfad nicht korrekt funktionierte.

### en

**Bug fixes:**

* Fixes a bug which caused the frontend route generation for URLs with a path not to work correctly.


## 1.2.0

### de

**Neue Funktionen und Verbesserungen:**

* Das Plugin unterstützt jetzt Digital Wallets (ApplePay, GooglePay), SEPA-Lastschrift und Klarna als Zahlungsarten.
* Das Plugin unterstützt jetzt Shopware 6.2.
* Das Plugin aktiviert und deaktiviert die Zahlungsarten jetzt automatisch, wenn es aktiviert bzw. deaktiviert wird.

**Voraussetzungen:**

* Das Plugin erfordert jetzt mindestens Shopware 6.2.0.

### en

**New features and enhancements:**

* The plugin now supports Digital Wallets (ApplePay, GooglePay), SEPA direct debit and Klarna as payment methods.
* The plugin now supports Shopware 6.2.0.
* The plugin now automatically enables and disables payment methods when enabled or disabled.

**Requirements:**

* The plugin now requires at least Shopware 6.2.0.


## 1.1.0

### de

**Neue Funktionen und Verbesserungen:**

* Das Plugin unterstützt jetzt Shopware 6.1.6.

**Voraussetzungen:**

* Das Plugin erfordert mindestens Shopware 6.1.6.

### en

**New features and enhancements:**

* The plugin now supports Shopware 6.1.6.

**Requirements:**

* The plugin requires at least Shopware 6.1.6.


## 1.0.0

### de

**Initiales Release mit folgenden Zahlungsarten:**

* Kreditkarte
* SOFORT

### en

**Initial release with these payment types:**

* Credit card
* SOFORT

## 1.2.1

### de

* Behebt einen Fehler, der dazu führte, dass die Frontend Routen-Generierung bei URLs mit Pfad nicht korrekt funktionierte.

### en

* Fixes a bug which caused the frontend route generation for URLs with a path not to work correctly.


## 1.2.0

### de

**Neue Funktionen und Verbesserungen:**

* Das Plugin unterstützt jetzt Digital Wallets (ApplePay, GooglePay), SEPA-Lastschrift und Klarna als Zahlungsarten.
* Das Plugin unterstützt jetzt Shopware 6.2.
* Das Plugin aktiviert und deaktiviert die Zahlungsarten jetzt automatisch, wenn es aktiviert bzw. deaktiviert wird.

**Voraussetzungen:**

* Das Plugin erfordert jetzt mindestens Shopware 6.2.0.

### en

**New features and enhancements:**

* The plugin now supports Digital Wallets (ApplePay, GooglePay), SEPA direct debit and Klarna as payment methods.
* The plugin now supports Shopware 6.2.0.
* The plugin now automatically enables and disables payment methods when enabled or disabled.

**Requirements:**

* The plugin now requires at least Shopware 6.2.0.


## 1.1.0

### de

**Neue Funktionen und Verbesserungen:**

* Das Plugin unterstützt jetzt Shopware 6.1.6.

**Voraussetzungen:**

* Das Plugin erfordert mindestens Shopware 6.1.6.

### en

**New features and enhancements:**

* The plugin now supports Shopware 6.1.6.

**Requirements:**

* The plugin requires at least Shopware 6.1.6.


## 1.0.0

### de

**Initiales Release mit folgenden Zahlungsarten:**

* Kreditkarte
* SOFORT

### en

**Initial release with these payment types:**

* Credit card
* SOFORT
