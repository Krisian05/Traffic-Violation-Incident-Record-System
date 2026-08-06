<?php

namespace App\Services;

class TvrsKnowledgeBase
{
    /**
     * Get system prompt for Gemini API with complete TVIRS LGU system knowledge.
     */
    public static function getSystemPrompt(string $userName = 'User', string $userRole = 'User', string $lguName = 'LGU', array $stats = []): string
    {
        $statsSection = "";
        if (!empty($stats)) {
            $statsSection = "\nCURRENT LIVE DATABASE STATS FOR THIS LGU:\n";
            $statsSection .= "- Total Registered Motorists: " . number_format($stats['total_motorists'] ?? 0) . "\n";
            $statsSection .= "- Total Registered Vehicles: " . number_format($stats['total_vehicles'] ?? 0) . "\n";
            $statsSection .= "- Total Issued Violations: " . number_format($stats['total_violations'] ?? 0) . "\n";
            $statsSection .= "- Unsettled Citations: " . number_format($stats['unsettled_violations'] ?? 0) . "\n";
            $statsSection .= "- Settled Citations: " . number_format($stats['settled_violations'] ?? 0) . "\n";
            $statsSection .= "- Total Logged Incidents: " . number_format($stats['total_incidents'] ?? 0) . "\n";
            $statsSection .= "- Total Collections: ₱" . number_format($stats['total_collections'] ?? 0, 2) . "\n";
        }

        return <<<EOT
You are TVIRS AI Assistant, the official automated support specialist for the Traffic Violation Incident Record System (TVIRS).
You are currently assisting {$userName} (Role: {$userRole}) operating under {$lguName}.
{$statsSection}
YOUR CORE GOAL:
Provide precise, step-by-step guidance on how to navigate and use all LGU functions, menus, features, and troubleshooting workflows in the TVIRS application. If the user asks for current live counts (e.g. how many motorists, violations, incidents, or total collections), answer using the exact Live Database Stats provided above.

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
   - Incidents (/incidents): Log traffic accidents, hit-and-run events, or major road incidents. Supports attaching multiple photos/video evidence and mapping locations.
   - Charge Types (/incident-charge-types): Configure administrative and criminal incident charge categories (e.g. Reckless Driving with Damage to Property).

6. AUDIT LOGS & AUDIT TRAIL (/audit-logs):
   - Accessing Audit Trail: Authorized roles (Admin, LGU Admin, Auditor, Traffic Supervisor) can view complete activity logs at /audit-logs.
   - Audited Events: Tracks user logins/logouts, ticket creations, payment settlements, voided OR transactions, and system configuration updates.

7. VIOLATION TYPES & PENALTIES (/violation-types):
   - LGU admins configure violation types, penalty fees for 1st, 2nd, and 3rd offenses, and whether impounding is mandatory.

8. REPORTS & RECONCILIATION (/payments/report, /reports):
   - Collection Reports (/payments/report): Summarizes daily collections by Cashier/OR number. Exportable to Excel for LGU Treasury reconciliation.
   - Reports (/reports): Statistical charts on top violations, peak violation hours, officer performance, and downloadable PDF/Excel reports.

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
                'keywords' => ['add user', 'create user', 'new user', 'register user', 'add enforcer', 'user account', 'how to add user'],
                'answer' => "👤 **How to Add a New User Account in TVIRS**\n\n1. Go to **Users** (`/users`) under the *Administration* section on the left sidebar.\n2. Click **+ Create New User**.\n3. Enter the full **Name**, **Username**, **Email**, and select the **Role** (LGU Admin, Cashier, Traffic Supervisor, Issuing Officer/Enforcer, or Auditor).\n4. Set a strong password and select your **LGU**.\n5. Click **Save User**.\n\n*Tip: For field Enforcers, pair their smartphone device under User Details ➔ Registered Devices.*"
            ],
            [
                'id' => 'faq_audit_log',
                'question' => 'How to see system audit logs?',
                'keywords' => ['see audit', 'audit log', 'audit trail', 'view audit', 'audit logs', 'how to see audit', 'logs'],
                'answer' => "🛡️ **Viewing System Audit Logs**\n\n1. Go to **Audit Trail** (`/audit-logs`) on the left sidebar under *References*.\n2. Review timestamped security logs tracking:\n   - User login & logout events\n   - Ticket creations, updates, and voids\n   - Cashier payment settlements & OR number corrections\n   - User account modifications & SMS configuration changes\n3. Use the search bar or date filters to find specific activity records."
            ],
            [
                'id' => 'faq_charge_type',
                'question' => 'How to view or manage incident charge types?',
                'keywords' => ['chagre type', 'charge type', 'incident charge', 'see charge', 'charge types', 'how to see chagre type'],
                'answer' => "⚖️ **Viewing Incident Charge Types**\n\n1. Go to **Charge Types** (`/incident-charge-types`) on the left sidebar under *References*.\n2. View the list of administrative and criminal incident charge categories.\n3. LGU Admins can click **+ Add Charge Type** or **Edit** to update fine classifications or descriptions."
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
                'keywords' => ['add motorist', 'setup motorist', 'create motorist', 'driver record', 'new violator', 'see motorist'],
                'answer' => "🚗 **Adding & Searching Motorists**\n\n1. Go to **Motorists** (`/violators`) on the left sidebar.\n2. Search by **Driver's License Number** or **Name** to check existing records.\n3. To add a new driver, click **+ Add Motorist**.\n4. Fill in License Number, Full Name, Contact Number, Address, and License Expiry Date.\n5. Click **Save Motorist**."
            ],
            [
                'id' => 'faq_vehicle_info',
                'question' => 'How to view vehicle directory?',
                'keywords' => ['vehicle', 'vehicles', 'plate number', 'see vehicle', 'impound vehicle'],
                'answer' => "🚘 **Vehicle Directory & Impound Records**\n\n1. Go to **Vehicles** (`/vehicles`) on the left sidebar under *Records*.\n2. Search by **License Plate Number** or **Chassis Number**.\n3. View vehicle ownership details, impound status, and attached citation photo evidence."
            ],
            [
                'id' => 'faq_incidents_info',
                'question' => 'How to check or log road incidents?',
                'keywords' => ['incidents', 'incident stats', 'log incident', 'traffic accident', 'road incident', 'many incidents', 'see incident'],
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
                'keywords' => ['collection report', 'treasury', 'daily collection', 'excel report', 'reconciliation', 'see report'],
                'answer' => "📊 **Generating Daily Collection Reports**\n\n1. Go to **Collection Reports** (`/payments/report`).\n2. Filter collections by **Date Range**, **Cashier Name**, or **Payment Method**.\n3. Review total collections, OR number ranges, and transaction counts.\n4. Click **Export to Excel** to download the spreadsheet for LGU Treasury turn-over and audit reconciliation."
            ],
        ];
    }
}
