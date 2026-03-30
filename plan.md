# 🏥 OmniPOS: Project Development Plan
> **Target:** Pharmacy & General Retail Management System  
> **Core Stack:** Laravel lates, Filament latest, Livewire latest  
> **Status:** Planning Phase

---

## 🏗️ 1. System Architecture
সিস্টেমটি একটি **Modular Monolith** পদ্ধতিতে তৈরি হবে যাতে এটি দ্রুত কাজ করে এবং মেইনটেন্যান্স সহজ হয়।

* **Backend:** Laravel 13 (High performance with PHP 8.4 features)
* **Admin UI:** Filament v3 (TALL Stack - Tailwind, Alpine, Laravel, Livewire)
* **Database:** PostgreSQL/MySQL (With optimized indexing for SKU & Invoice)
* **API:** Laravel Sanctum (Mobile App integration এর জন্য প্রস্তুত)

---

## 📅 2. Development Roadmap (Timeline)

### 🟢 Phase 1: Core Foundation (Week 1)
* **Authentication & RBAC:** এডমিন, ম্যানেজার এবং স্টাফ রোল ডিফাইন করা।
* **Dynamic Settings:** 'Pharmacy Mode' অন/অcomposer require filament/filament:"^5.0"

php artisan filament:install --panelsফ করার গ্লোবাল সেটিংস তৈরি।
* **Master Catalog:** ক্যাটাগরি, ব্র্যান্ড এবং ইউনিট ম্যানেজমেন্ট।
* **Product & Variant Engine:** একই মেইন প্রোডাক্টের আন্ডারে বিভিন্ন সাইজ/প্যাক (SKU ভিত্তিক) এন্ট্রি।

### 🟡 Phase 2: Inventory & Batch Logic (Week 2)
* **Batch Management:** প্রতিটি ভ্যারিয়েন্টের জন্য আলাদা ব্যাচ, কেনা দাম এবং এক্সপায়ারি ডেট।
* **Stock Tracking:** রিয়েল-টাইম স্টক আপডেট এবং 'Low Stock' অ্যালার্ট।
* **FEFO Implementation:** মেয়াদ শেষ হওয়ার ভিত্তিতে স্টকের অগ্রাধিকার নির্ধারণ (First Expired First Out)।

### 🔴 Phase 3: High-Speed POS Interface (Week 3)
* **Interactive POS Screen:** কি-বোর্ড শর্টকাট এবং বারকোড স্ক্যানার সাপোর্ট।
* **Conditional UI:** 'Pharmacy Mode' এর উপর ভিত্তি করে জেনেরিক নাম বা এক্সপায়ারি কলাম দেখানো/লুকানো।
* **Transaction Logic:** হোল্ড সেল, ডিসকাউন্ট, ডিউ এবং মাল্টি-মেথড পেমেন্ট (Cash, bKash)।
* **Thermal Printing:** সরাসরি থার্মাল প্রিন্টারে ইনভয়েস জেনারেট।

### 🔵 Phase 4: Reports & API Ready (Week 4)
* **Financial Insights:** ব্যাচ-ভিত্তিক নিট প্রফিট ক্যালকুলেশন।
* **Expiry Forecaster:** আগামী ৬০-৯০ দিনের মধ্যে মেয়াদ শেষ হবে এমন পণ্যের রিপোর্ট।
* **Mobile API:** মোবাইল অ্যাপের জন্য প্রয়োজনীয় এন্ডপয়েন্ট (Sanctum Auth) তৈরি।

---

## 🧬 3. Variant & Batch Implementation Logic

সিস্টেমটি নিচের ফ্লো অনুযায়ী ডাটা প্রসেস করবে:

1.  **Product:** Napa (Paracetamol)
2.  **Variant:** 500mg Strip (SKU: NAPA-500)
3.  **Batch:** B-102 (Expiry: 2027, Purchase: ৳১০, Sell: ৳১২)
4.  **POS Action:** স্ক্যান NAPA-500 -> ব্যাচ B-102 থেকে স্টক মাইনাস -> সেলস রেকর্ড তৈরি।

---

## ⚡ 4. Speed & Cost Optimization Strategy

| কৌশল (Strategy) | বাস্তবায়নের উপায় (Implementation) |
| :--- | :--- |
| **Database Indexing** | `sku`, `invoice_no`, `phone` কলামে ইনডেক্স ব্যবহার। |
| **Caching** | ক্যাটাগরি এবং সেটিংস ডাটা **Redis Cache** এ রাখা। |
| **Octane Ready** | হাই-ট্রাফিক হ্যান্ডেল করতে **Laravel Octane** সাপোর্ট রাখা। |
| **Low-Cost Hosting** | শুরুতে $5 VPS (Hetzner/DigitalOcean) এবং Docker ব্যবহার। |

---

## 📱 5. Future Mobile App Roadmap
* **Camera Scanner:** ফোনের ক্যামেরা দিয়ে সরাসরি বারকোড স্ক্যান করে সেল।
* **Owner's Dashboard:** মালিক দূরে থাকলেও মোবাইলে লাভ-ক্ষতির লাইভ আপডেট।
* **Offline Sync:** ইন্টারনেটের অনুপস্থিতিতেও ডাটা ক্যাশ করে রাখার ব্যবস্থা।

---