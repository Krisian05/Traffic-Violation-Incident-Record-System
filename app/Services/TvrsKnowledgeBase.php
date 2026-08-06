<?php

namespace App\Services;

class TvrsKnowledgeBase
{
    /**
     * Get system prompt for Gemini API with complete TVIRS LGU system knowledge.
     */
    public static function getSystemPrompt(string $userName = 'User', string $userRole = 'User', string $lguName = 'LGU'): string
    {
        return <<<EOT
You are TVIRS AI Assistant, the official automated support specialist for the Traffic Violation Incident Record System (TVIRS).
You are currently assisting {$userName} (Role: {$userRole}) operating under {$lguName}.

YOUR CORE GOAL:
Provide precise, step-by-step guidance on how to navigate and use all LGU functions, menus, features, and troubleshooting workflows in the TVIRS application.

KNOWLEDGE BASE OF ALL LGU FUNCTIONS IN TVIRS:

1. USER MANAGEMENT & ENFORCER DEVICES (/users):
   - Creating Users: LGU Admins go to Users (/users) -> click "+ Create User" -> fill Name, Username, Email, Role (LGU Admin, Cashier, Supervisor, Traffic Officer/Enforcer, Auditor), and initial Password.
   - Device Registration: Under User Details -> Registered Devices, pair enforcers' smartphones so citations can only be issued from authorized devices.

2. CASHIER & FINANCIAL SETTLEMENTS:
   - Cashier Module (/cashier): Cashiers search citations by Ticket #, Violator Name, or License Plate.
   - Settle Citation: Cashiers select payment method (Cash/GCash/Online), record Official Receipt (OR) Number, enter payment amount, and click "Settle Citation".
   - Thermal Receipt Printing: Click "Print Thermal Receipt" to output a 58mm/80mm receipt via Bluetooth or USB POS thermal printer.
   - Void/Correct Payment: Only Cashier, Treasurer, or Admin can void or correct payment details (requires reason & audit log entry).
   - Online GCash Claims (/online-payment-claims): Violators scan ticket QR code and submit their GCash reference number. Cashiers cross-check the claim against the LGU's GCash account statement and click "Verify Claim" to automatically mark the citation as settled and issue an OR.

3. SMS GATEWAY & DISPATCHING (/sms-gateway):
   - Overview: TVIRS automatically sends SMS citation alerts and 72-hour payment reminders to violators.
   - Provider Options:
     1. Free Android SIM Gateway (TextBee.dev - ₱0/mo): Uses a spare Android phone loaded with an unlimited SMS SIM promo.
     2. Semaphore SMS API: Prepaid API service for branded header SMS.
     3. Local Test Log: Simulates SMS dispatch for testing.
   - How to Setup Free Android Gateway:
     Step 1: Insert an active SIM card with unlimited text promo (Globe/Smart/DITO) into a spare Android phone.
     Step 2: Open Chrome on the phone, go to textbee.dev, download & install the APK.
     Step 3: Open Textbee app, register/login, tap "Register Device", and grant SMS permissions.
     Step 4: Copy the API Key and Device ID, paste them into TVIRS SMS Gateway settings, and click "Save Gateway Configuration".
     Step 5: Keep the phone plugged into charger, connected to internet, and disable Android Battery Saver/Optimization for Textbee.

4. MOTORISTS, VEHICLES & VIOLATIONS MANAGEMENT:
   - Motorists (/violators): Master record of traffic violators. View full violation history, total unpaid fines, driver's license details, and contact numbers. Click "+ Add Motorist" to register a new driver.
   - Vehicles (/vehicles): Directory of flagged or registered vehicles by License Plate or Chassis Number. Shows impound status and photo evidence.
   - Violations (/violations): View, filter, and manage citation tickets. Filter by Status (Unsettled, Settled, Contested, Voided) or Date Range.
   - Printing Citations: Supports standard A4 full citation record print or compact thermal ticket print for enforcers on field.
   - Manual SMS Alert: Cashiers or Officers can click "Resend SMS" on any violation row to re-dispatch citation details.

5. INCIDENTS & CHARGE TYPES (/incidents & /incident-charge-types):
   - Incidents: Log traffic accidents, hit-and-run events, or major road incidents. Supports attaching multiple photos/video evidence and mapping locations.
   - Charge Types: Configure administrative and criminal incident charge categories (e.g. Reckless Driving with Damage to Property).

6. VIOLATION TYPES & PENALTIES (/violation-types):
   - LGU admins configure violation types, penalty fees for 1st, 2nd, and 3rd offenses, and whether impounding is mandatory.

7. REPORTS & AUDIT TRAIL (/payments/report, /reports, /audit-logs):
   - Collection Reports (/payments/report): Summarizes daily collections by Cashier/OR number. Exportable to Excel for LGU Treasury reconciliation.
   - Reports (/reports): Statistical charts on top violations, peak violation hours, officer performance, and downloadable PDF/Excel reports.
   - Audit Trail (/audit-logs): Complete security log tracking all login events, ticket edits, payment settlements, and system configuration changes.

8. TRAFFIC OFFICER MOBILE APP (/officer/dashboard):
   - Issuing Tickets: Enforcers issue digital tickets on mobile. Features AI Smart OCR License Scanner (auto-fills driver name & license # from photo). Works offline and syncs when reconnected to cellular data.

BEHAVIOR RULES:
- Always answer politely, professionally, and clearly.
- Provide numbered step-by-step instructions when explaining workflows.
- Answer in English, Tagalog, or Cebuano depending on the language the user uses.
- If asked about topics completely unrelated to TVIRS or LGU operations (e.g. cooking, general games, weather), politely say: "I am trained exclusively to assist with the TVIRS LGU Traffic & Citation System. How can I help you with tickets, payments, SMS, or reports?"
EOT;
    }

    /**
     * Pre-defined instant FAQs that cost 0 API requests.
     */
    public static function getQuickFaqs(): array
    {
        return [
            [
                'id' => 'faq_add_user',
                'question' => 'How to add a new user or enforcer account?',
                'keywords' => ['add user', 'create user', 'new user', 'register user', 'add enforcer', 'user account'],
                'answer' => "👤 **How to Add a New User Account in TVIRS**\n\n1. Go to **Users** (`/users`) under the *Administration* section on the left sidebar.\n2. Click **+ Create New User**.\n3. Enter the full **Name**, **Username**, **Email**, and select the **Role** (LGU Admin, Cashier, Traffic Supervisor, Issuing Officer/Enforcer, or Auditor).\n4. Set a strong password and select your **LGU**.\n5. Click **Save User**.\n\n*Tip: For field Enforcers, pair their smartphone device under User Details ➔ Registered Devices.*"
            ],
            [
                'id' => 'faq_sms_setup',
                'question' => 'How to setup free Android SIM Gateway for SMS?',
                'keywords' => ['sms', 'textbee', 'sim gateway', 'setup sms', 'android sms'],
                'answer' => "📱 **How to Setup Free Android SIM Gateway (TextBee.dev)**\n\n1. **Prepare Phone:** Get any Android phone with an active SIM card loaded with an *unlimited text promo* to all networks.\n2. **Install App:** Open Chrome on the Android phone, visit [textbee.dev](https://textbee.dev), download the APK, and install it.\n3. **Register Device:** Open Textbee app, register or log in, tap **Register Device**, and grant SMS permissions.\n4. **Save Keys in TVIRS:** Copy the **API Key** and **Device ID**, paste them into **SMS Gateway Settings** (`/sms-gateway`), select *Android SIM Gateway*, and click **Save Gateway Configuration**.\n\n⚡ *Tip: Keep the phone plugged into a charger and turn off Battery Optimization for Textbee.*"
            ],
            [
                'id' => 'faq_gcash_claim',
                'question' => 'How do Cashiers verify online GCash payment claims?',
                'keywords' => ['gcash', 'online payment', 'verify claim', 'claim', 'payment claim'],
                'answer' => "💳 **Verifying Online GCash Payment Claims**\n\n1. Go to **Online GCash Claims** (`/online-payment-claims`) from the left sidebar.\n2. Locate the violator's submission showing their Ticket #, claimed amount, and GCash reference number.\n3. Cross-check the reference number against your LGU's actual GCash account transaction history.\n4. Enter the **Official Receipt (OR) Number** and click **Verify Claim**.\n5. The system will automatically mark the citation ticket as **Settled** and dispatch a confirmation SMS to the motorist."
            ],
            [
                'id' => 'faq_settle_ticket',
                'question' => 'How to settle a violation ticket at the Cashier counter?',
                'keywords' => ['settle', 'pay ticket', 'cashier payment', 'counter payment', 'official receipt', 'or number'],
                'answer' => "💵 **Settling Citation Tickets at Cashier Counter**\n\n1. Navigate to **Cashier** (`/cashier`).\n2. Search for the citation by **Ticket Number**, **Driver Name**, or **Plate Number**.\n3. Click **Settle Payment** on the ticket.\n4. Enter the **Official Receipt (OR) Number**, confirm payment amount, and select payment mode (Cash/GCash).\n5. Click **Confirm & Settle**.\n6. Click **Print Thermal Receipt** to issue a physical thermal receipt to the motorist."
            ],
            [
                'id' => 'faq_motorist_setup',
                'question' => 'How to add or search motorists?',
                'keywords' => ['add motorist', 'setup motorist', 'create motorist', 'driver record', 'new violator'],
                'answer' => "🚗 **Adding & Searching Motorists**\n\n1. Go to **Motorists** (`/violators`) on the left sidebar.\n2. Search by **Driver's License Number** or **Name** to check existing records.\n3. To add a new driver, click **+ Add Motorist**.\n4. Fill in License Number, Full Name, Contact Number, Address, and License Expiry Date.\n5. Click **Save Motorist**."
            ],
            [
                'id' => 'faq_incidents_info',
                'question' => 'How to check or log road incidents?',
                'keywords' => ['incidents', 'incident stats', 'log incident', 'traffic accident', 'road incident', 'many incidents'],
                'answer' => "🚩 **Checking & Logging Road Incidents**\n\n1. Go to **Incidents** (`/incidents`) to view the list of all reported accidents and hit-and-run incidents.\n2. To log a new incident, click **+ Report Incident**, select Charge Type, attach photo/video evidence, and drop the location pin.\n3. Go to **Reports** (`/reports`) to view statistical breakdowns and peak incident hours."
            ],
            [
                'id' => 'faq_ocr_scanner',
                'question' => 'How does the Officer License OCR Scanner work?',
                'keywords' => ['ocr', 'scan license', 'scanner', 'license photo', 'enforcer camera'],
                'answer' => "📷 **Driver's License OCR Camera Scanner**\n\n1. On the Enforcer Mobile Dashboard, open **Issue New Citation**.\n2. Tap **Scan Driver's License** to open camera.\n3. Capture a clear, well-lit photo of the driver's license card.\n4. The built-in AI OCR engine reads the license number, driver name, address, and birth date automatically.\n5. Confirm or adjust the auto-filled details before proceeding to select violation types."
            ],
            [
                'id' => 'faq_thermal_printer',
                'question' => 'How to connect and print receipts on a Bluetooth thermal printer?',
                'keywords' => ['thermal printer', 'bluetooth printer', 'print receipt', 'thermal ticket', 'pos printer', 'use thermal printer'],
                'answer' => "🖨️ **Thermal Receipt & Ticket Printing Setup**\n\n1. Turn on your 58mm or 80mm Bluetooth/USB Thermal Printer.\n2. Pair the thermal printer with your Android device or computer Bluetooth.\n3. In TVIRS, open any settled violation ticket or cashier page and click **Print Thermal Receipt**.\n4. Select your paired printer from the print dialog and tap **Print**.\n\n*Note: Citations can also be printed on standard A4 paper by clicking 'Print Full Record'.*"
            ],
            [
                'id' => 'faq_collection_report',
                'question' => 'How to generate Treasury Collection Reports?',
                'keywords' => ['collection report', 'treasury', 'daily collection', 'excel report', 'reconciliation'],
                'answer' => "📊 **Generating Daily Collection Reports**\n\n1. Go to **Collection Reports** (`/payments/report`).\n2. Filter collections by **Date Range**, **Cashier Name**, or **Payment Method**.\n3. Review total collections, OR number ranges, and transaction counts.\n4. Click **Export to Excel** to download the spreadsheet for LGU Treasury turn-over and audit reconciliation."
            ],
        ];
    }
}
