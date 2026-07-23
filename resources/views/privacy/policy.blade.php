@extends('layouts.app')
@section('title', 'Data Privacy Compliance & Policy')

@section('content')
<div class="container py-4" style="max-width:1000px;">

    {{-- Header Banner --}}
    <div class="p-4 mb-4 rounded-3 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); border-left: 6px solid #3b82f6;">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-shield-check display-5 text-warning"></i>
            <div>
                <h3 class="fw-700 mb-1">Data Privacy Compliance & Governance Framework</h3>
                <p class="mb-0 text-white-50" style="font-size:.9rem;">
                    Republic Act No. 10173 (Data Privacy Act of 2012) &amp; National Privacy Commission (NPC) Compliance Notice
                </p>
            </div>
        </div>
    </div>

    {{-- Navigation Tabs / Quick Jump --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="#notice" class="btn btn-sm btn-outline-primary"><i class="bi bi-info-circle me-1"></i>1. Privacy Notice</a>
        <a href="#dsa" class="btn btn-sm btn-outline-primary"><i class="bi bi-building-gear me-1"></i>2. LGU Data Sharing (DSA)</a>
        <a href="#dpa" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-lock me-1"></i>3. Service Provider DPA</a>
        <a href="#access" class="btn btn-sm btn-outline-primary"><i class="bi bi-person-lock me-1"></i>4. Access Control Policy</a>
        <a href="#retention" class="btn btn-sm btn-outline-primary"><i class="bi bi-clock-history me-1"></i>5. Data Retention Policy</a>
        <a href="#breach" class="btn btn-sm btn-outline-primary"><i class="bi bi-exclamation-triangle me-1"></i>6. Data Breach Protocol</a>
        <a href="{{ route('privacy.dsr') }}" class="btn btn-sm btn-warning fw-600"><i class="bi bi-pencil-square me-1"></i>7. Submit Data Subject Request</a>
    </div>

    {{-- Section 1: Privacy Notice --}}
    <div class="card border-0 shadow-sm mb-4" id="notice">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-700 text-primary mb-0"><i class="bi bi-file-text me-2"></i>1. Privacy Notice for Violators &amp; Citizens</h5>
        </div>
        <div class="card-body pt-0" style="font-size:.92rem; color:#334155; line-height:1.6;">
            <p>The Traffic Violations and Incidents Recording System (TVIRS), operated by the Provincial Government of Cebu in coordination with the Philippine National Police (PNP) and participating Local Government Units (LGUs), processes personal data strictly in accordance with Republic Act No. 10173 (Data Privacy Act of 2012).</p>

            <h6 class="fw-700 text-dark mt-3">A. Information Collected</h6>
            <ul>
                <li><strong>Driver &amp; Violator Information:</strong> Full name, driver's license number, license type, address, contact number, gender, date of birth, driver photo, valid ID photo, and digital signature.</li>
                <li><strong>Vehicle Information:</strong> Plate number, chassis number, engine number, make, model, color, vehicle classification, OR/CR numbers, registered owner name.</li>
                <li><strong>Enforcement &amp; Incident Data:</strong> Date, time, location address, geographic GPS coordinates, citation ticket photos, vehicle damage photos, officer notes.</li>
                <li><strong>Payment Transaction Data:</strong> Official Receipt (OR) numbers, amounts paid, payment channels (Cash, GCash, Maya, Bank), payment dates, cashier details.</li>
            </ul>

            <h6 class="fw-700 text-dark mt-3">B. Purpose of Processing</h6>
            <p>Personal information is collected exclusively for legitimate public governance and enforcement functions, including:</p>
            <ul>
                <li>Electronic issuance and digital recording of traffic citation tickets and incident reports.</li>
                <li>Automated calculation of fines, late penalties, and payment collection tracking.</li>
                <li>Resolution of contested citations, recidivism tracking, and law enforcement auditing.</li>
                <li>Consolidated provincial traffic safety monitoring and policy analytics across Cebu LGUs.</li>
            </ul>

            <h6 class="fw-700 text-dark mt-3">C. Data Protection Rights</h6>
            <p>Under RA 10173, data subjects have the right to be informed, right to access, right to object, right to erasure or blocking, right to rectification, right to data portability, and the right to lodge a complaint with the National Privacy Commission (NPC).</p>
        </div>
    </div>

    {{-- Section 2: Data Sharing Agreements (DSA) --}}
    <div class="card border-0 shadow-sm mb-4" id="dsa">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-700 text-primary mb-0"><i class="bi bi-diagram-3 me-2"></i>2. Inter-Agency Data Sharing Agreements (DSA)</h5>
        </div>
        <div class="card-body pt-0" style="font-size:.92rem; color:#334155; line-height:1.6;">
            <p>Data sharing between participating LGUs, the Provincial Government of Cebu, and law enforcement agencies is governed by formal Data Sharing Agreements compliant with NPC Circular 16-02:</p>
            <ul>
                <li><strong>Data Ownership &amp; Segregation:</strong> LGUs maintain primary ownership of local enforcement records created within their jurisdiction, isolated via automated system tenant boundaries.</li>
                <li><strong>Provincial Consolidation:</strong> Consolidated data access is limited to aggregated statistical reporting, revenue collection summaries, and cross-boundary traffic analytics.</li>
                <li><strong>Security Obligations:</strong> All participating entities enforce strict role-based access control, encrypted transmission, and confidentiality non-disclosure.</li>
            </ul>
        </div>
    </div>

    {{-- Section 3: Data Processing Agreement (DPA) --}}
    <div class="card border-0 shadow-sm mb-4" id="dpa">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-700 text-primary mb-0"><i class="bi bi-file-earmark-code me-2"></i>3. Technology Service Provider DPA</h5>
        </div>
        <div class="card-body pt-0" style="font-size:.92rem; color:#334155; line-height:1.6;">
            <p>Technical service providers and cloud infrastructure vendors operate under a formal Data Processing Agreement (DPA) as Data Processors pursuant to NPC Circular 16-01:</p>
            <ul>
                <li>Processing is conducted strictly upon documented instructions from the Personal Information Controller (PIC).</li>
                <li>Technical controls include TLS 1.3 encrypted transmission, AES-256 database storage encryption, and strict non-disclosure obligations.</li>
                <li>Sub-processors are bound by identical privacy and security standards.</li>
            </ul>
        </div>
    </div>

    {{-- Section 4: Access Control Policy --}}
    <div class="card border-0 shadow-sm mb-4" id="access">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-700 text-primary mb-0"><i class="bi bi-shield-lock me-2"></i>4. Documented Access Control Policy</h5>
        </div>
        <div class="card-body pt-0" style="font-size:.92rem; color:#334155; line-height:1.6;">
            <p>Access privileges within TVIRS strictly follow the Principle of Least Privilege and Role-Based Access Control (RBAC):</p>
            <ul>
                <li><strong>Super Administrator:</strong> System administration, global configuration, user provisioning.</li>
                <li><strong>Provincial Administrator:</strong> Province-wide monitoring, consolidated analytics, aggregated collection reports.</li>
                <li><strong>LGU Administrator:</strong> LGU user management, local citation &amp; incident management.</li>
                <li><strong>Treasurer / Cashier:</strong> Exclusive access to payment validation, OR recording, and settlement functions.</li>
                <li><strong>Police Traffic Supervisor:</strong> Enforcement review, officer activity monitoring, operational reporting.</li>
                <li><strong>Issuing Officer:</strong> Field e-citation issuance and evidence capture (restricted to own-issued tickets).</li>
                <li><strong>Records Officer:</strong> Authorized record encoding, document maintenance, and record updates.</li>
                <li><strong>Auditor:</strong> Read-only access to approved reports and audit logs (mutation operations strictly blocked).</li>
            </ul>
        </div>
    </div>

    {{-- Section 5: Data Retention Policy --}}
    <div class="card border-0 shadow-sm mb-4" id="retention">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-700 text-primary mb-0"><i class="bi bi-calendar-check me-2"></i>5. Data Retention &amp; Disposal Policy</h5>
        </div>
        <div class="card-body pt-0" style="font-size:.92rem; color:#334155; line-height:1.6;">
            <p>Personal data and citation records are retained only for the duration necessary to fulfill operational, legal, and financial auditing requirements:</p>
            <ul>
                <li><strong>Active Citation Records:</strong> Pending and overdue citations are retained until full settlement or formal administrative/court disposition.</li>
                <li><strong>Settled Financial Transactions:</strong> Retained for five (5) years in compliance with COA auditing regulations.</li>
                <li><strong>System Audit Logs:</strong> System access logs and user audit trails are archived for three (3) years.</li>
                <li><strong>Secure Disposal:</strong> Expired records undergo cryptographic erasure and secure database purging via automated retention commands (`php artisan tvirs:retention-cleanup`).</li>
            </ul>
        </div>
    </div>

    {{-- Section 6: Data Breach Protocol --}}
    <div class="card border-0 shadow-sm mb-4" id="breach">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-700 text-primary mb-0"><i class="bi bi-shield-exclamation me-2"></i>6. Data Security Incident &amp; Breach Response Protocol</h5>
        </div>
        <div class="card-body pt-0" style="font-size:.92rem; color:#334155; line-height:1.6;">
            <p>TVIRS enforces a documented Data Breach Response Protocol per NPC Circular 16-03:</p>
            <ul>
                <li><strong>Detection &amp; Escalation:</strong> Security incidents trigger immediate automated logging and notification to the Data Protection Officer (DPO).</li>
                <li><strong>Containment &amp; Investigation:</strong> Technical teams isolate affected components, assess scope, and implement corrective measures.</li>
                <li><strong>Mandatory Notification:</strong> Affected data subjects and the National Privacy Commission (NPC) are notified within seventy-two (72) hours of breach verification when high-risk personal data is compromised.</li>
            </ul>
        </div>
    </div>

    {{-- Section 7: Data Subject Request Callout --}}
    <div class="p-4 rounded-3 bg-light border text-center">
        <h5 class="fw-700 text-dark mb-2">Need to Submit a Data Subject Request?</h5>
        <p class="text-muted mb-3" style="font-size:.9rem;">Under RA 10173, you may request access to your recorded citations, ask for data corrections, or submit privacy inquiries.</p>
        <a href="{{ route('privacy.dsr') }}" class="btn btn-warning px-4 fw-600">
            <i class="bi bi-pencil-square me-1"></i> Open Data Subject Request Portal
        </a>
    </div>

</div>
@endsection
