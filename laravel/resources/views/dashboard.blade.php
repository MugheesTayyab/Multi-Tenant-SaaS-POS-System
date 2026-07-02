<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enterprise SaaS POS System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js for premium analytical graphics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-color: #f8fafc;
            --sidebar-bg: #0f172a;
            --card-bg: #ffffff;
            --border-color: rgba(0, 0, 0, 0.08);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --form-bg: #f1f5f9;
            --row-hover: rgba(0, 0, 0, 0.02);
            --card-inner-bg: #f8fafc;
            --glass-blur: blur(16px);
            --font-family: 'Outfit', sans-serif;
            --sidebar-width: 260px;
            --transition-speed: 0.3s;
        }

        [data-theme="dark"] {
            --bg-color: #1e293b;
            --sidebar-bg: #0f172a;
            --card-bg: rgba(15, 23, 42, 0.45);
            --border-color: rgba(255, 255, 255, 0.09);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --form-bg: rgba(255, 255, 255, 0.05);
            --row-hover: rgba(255, 255, 255, 0.02);
            --card-inner-bg: rgba(255, 255, 255, 0.03);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: var(--font-family);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 99px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-secondary);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }

        /* --- AUTH SCREEN --- */
        #auth-screen {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        .auth-card {
            background: rgba(15, 23, 42, 0.35);
            backdrop-filter: blur(20px) saturate(120%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: fadeInUp 0.6s ease;
        }

        .auth-logo {
            text-align: center;
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(to right, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .auth-subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 30px;
        }

        /* --- MAIN SYSTEM STRUCTURE --- */
        #app-layout {
            display: none;
            flex-direction: row;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        aside {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            transition: background-color var(--transition-speed);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, #a855f7 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .brand:hover .brand-icon {
            transform: scale(1.1) rotate(10deg);
        }

        .brand-name {
            font-size: 20px;
            font-weight: 700;
        }

        .nav-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .nav-item.active a, .nav-item a:hover {
            color: var(--text-primary);
            background-color: rgba(99, 102, 241, 0.15);
            box-shadow: inset 3px 0 0 var(--primary);
        }

        .nav-footer {
            border-top: 1px solid var(--border-color);
            padding-top: 16px;
            margin-top: auto;
        }

        /* --- CONTENT MAIN WORKSPACE --- */
        main {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 40px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .header-title h1 {
            font-size: 28px;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* --- STYLED INTERACTIVE ELEMENTS --- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }

        .btn:active {
            transform: scale(0.96);
        }

        .btn-primary {
            background-color: var(--primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.25);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: scale(0.96) translateY(0);
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.08);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }

        .btn-danger {
            background-color: var(--danger);
            color: #fff;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            background-color: var(--form-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            background-color: var(--card-bg);
        }

        /* --- CARDS & GRID LAYOUTS --- */
        .grid {
            display: grid;
            gap: 24px;
            margin-bottom: 24px;
        }

        .grid-4 { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .grid-3 { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
        .grid-2 { grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); }

        .card {
            background-color: var(--card-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
            border-color: rgba(99, 102, 241, 0.25);
        }

        .metric-title {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .metric-value {
            font-size: 32px;
            font-weight: 700;
        }

        /* --- TABLE STYLING --- */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 16px;
            background-color: var(--card-inner-bg);
            border-bottom: 1.5px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            color: var(--text-primary);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: var(--row-hover);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge::before {
            content: "";
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: currentColor;
        }

        .badge-success { background-color: rgba(16, 185, 129, 0.12); color: var(--success); }
        .badge-warning { background-color: rgba(245, 158, 11, 0.12); color: var(--warning); }
        .badge-danger { background-color: rgba(239, 68, 68, 0.12); color: var(--danger); }

        #drawer-status-chip {
            padding: 6px 14px;
            font-size: 13px;
            letter-spacing: 0.01em;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* --- VIEW VIEWS CONTROLLERS --- */
        .view-pane {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .view-pane.active {
            display: block;
        }

        /* --- POS SCREEN LAYOUT --- */
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
            height: calc(100vh - 160px);
        }

        .pos-main {
            display: flex;
            flex-direction: column;
            gap: 20px;
            height: 100%;
            overflow-y: auto;
        }

        .pos-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
            height: 100%;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px;
        }

        .pos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
            overflow-y: auto;
        }

        .pos-product-card {
            background-color: var(--card-inner-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .pos-product-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .cart-list {
            flex-grow: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--card-inner-bg);
            border-radius: 12px;
            padding: 12px;
            border: 1px solid var(--border-color);
        }

        /* --- MODAL SYSTEM --- */
        .modal {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: var(--sidebar-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            width: 100%;
            max-width: 500px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            animation: scaleIn 0.3s ease;
        }

        /* --- NOTIFICATION CHIP --- */
        #notification-box {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            color: #fff;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3), 0 8px 10px -6px rgba(0,0,0,0.3);
            border-left: 4px solid var(--primary);
            min-width: 280px;
            animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            font-size: 14px;
            font-weight: 500;
        }

        .toast.success { border-left-color: var(--success); box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.1); }
        .toast.danger { border-left-color: var(--danger); box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.1); }
        .toast.warning { border-left-color: var(--warning); box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.1); }

        /* --- ANIMATIONS --- */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>

    <!-- ================= AUTH SCREEN ================= -->
    <div id="auth-screen">
        <div class="auth-card">
            <div class="auth-logo">Enterprise POS SaaS</div>
            <div class="auth-subtitle">Login to access your store terminal</div>
            <form id="login-form">
                <div class="form-group">
                    <label for="login-email">Email Address</label>
                    <input type="email" id="login-email" class="form-control" placeholder="name@company.com" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">Authenticate Roster</button>
            </form>
            <div style="text-align: center; margin-top: 24px; font-size: 13px;">
                <span style="color: var(--text-secondary);">New Business?</span>
                <a href="#" onclick="showRegisterModal()" style="color: var(--primary); text-decoration: none; font-weight: 600; margin-left: 6px;">Register Shop</a>
            </div>
        </div>
    </div>

    <!-- ================= MAIN APP LAYOUT ================= -->
    <div id="app-layout">
        <!-- Sidebar Navigation -->
        <aside>
            <div class="brand">
                <div class="brand-icon">P</div>
                <div class="brand-name">POS Console</div>
            </div>
            <ul class="nav-links">
                <li class="nav-item active" data-view="dashboard-view"><a href="#">Dashboard</a></li>
                <li class="nav-item" data-view="pos-view"><a href="#">POS Terminal</a></li>
                <li class="nav-item" data-view="catalog-view"><a href="#">Catalog</a></li>
                <li class="nav-item" data-view="inventory-view"><a href="#">Inventory</a></li>
                <li class="nav-item" data-view="purchases-view"><a href="#">Purchases</a></li>
                <li class="nav-item" data-view="customers-view"><a href="#">CRM (Customers)</a></li>
                <li class="nav-item" data-view="finance-view"><a href="#">Financials</a></li>
                <li class="nav-item" data-view="employees-view"><a href="#">HRM Staff</a></li>
                <li class="nav-item" data-view="reports-view"><a href="#">Analytics</a></li>
                <li class="nav-item" data-view="settings-view"><a href="#">Settings</a></li>
                <li class="nav-item superadmin-link" style="display: none;" data-view="superadmin-view"><a href="#" style="color: #a855f7;">SaaS Landlord</a></li>
            </ul>
            <div class="nav-footer">
                <div style="font-size: 13px; font-weight: 600;" id="user-display-name">Cashier Profile</div>
                <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 12px;" id="user-display-branch">Main Branch</div>
                <button onclick="handleLogout()" class="btn btn-secondary" style="width: 100%; justify-content: center; font-size: 13px; padding: 8px 12px;">Logout Shift</button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main>
            <!-- Header bar -->
            <header>
                <div class="header-title">
                    <h1 id="view-title">Dashboard Overview</h1>
                </div>
                <div class="header-actions">
                    <button onclick="toggleTheme()" class="btn btn-secondary" style="padding: 10px;">🌓 Mode</button>
                    <div id="drawer-status-chip" class="badge badge-danger">Shift Drawer: Closed</div>
                </div>
            </header>

            <!-- ================= VIEW PANES ================= -->

            <!-- 1. DASHBOARD VIEW -->
            <div id="dashboard-view" class="view-pane active">
                <div class="grid grid-4">
                    <div class="card">
                        <div class="metric-title">Daily Sales</div>
                        <div class="metric-value" id="dash-daily-sales">$0.00</div>
                    </div>
                    <div class="card">
                        <div class="metric-title">Monthly Sales</div>
                        <div class="metric-value" id="dash-monthly-sales">$0.00</div>
                    </div>
                    <div class="card">
                        <div class="metric-title">Purchase Outlays</div>
                        <div class="metric-value" id="dash-purchase-outlays">$0.00</div>
                    </div>
                    <div class="card">
                        <div class="metric-title">Inventory Stocks</div>
                        <div class="metric-value" id="dash-stock-quantity">0 pcs</div>
                    </div>
                </div>

                <div class="grid grid-2">
                    <!-- Recent Invoices -->
                    <div class="card">
                        <h3 style="margin-bottom: 16px;">Recent Transactions</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Branch</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="dash-recent-sales-rows">
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top Selling Items -->
                    <div class="card">
                        <h3 style="margin-bottom: 16px;">Top Performing SKUs</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product Variant</th>
                                        <th>Qty Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody id="dash-top-products-rows">
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. POS VIEW -->
            <div id="pos-view" class="view-pane">
                <div class="pos-container">
                    <div class="pos-main">
                        <div class="card" style="padding: 16px; display: flex; gap: 16px; align-items: center;">
                            <input type="text" id="pos-search-input" class="form-control" placeholder="Scan barcode or type name to add to cart..." style="flex-grow: 1;">
                            <button onclick="simulateBarcodeScan()" class="btn btn-secondary">Simulate Scan</button>
                        </div>
                        <div class="pos-grid" id="pos-item-grid">
                            <!-- Dynamic Cards -->
                        </div>
                    </div>
                    <!-- Cart sidebar -->
                    <div class="pos-sidebar">
                        <h3 style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 16px;">Current Cart</h3>
                        <div class="cart-list" id="pos-cart-items">
                            <div style="text-align: center; color: var(--text-secondary); margin-top: 40px;">Cart is empty</div>
                        </div>
                        <div style="border-top: 1px solid var(--border-color); padding-top: 16px; display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span>Subtotal</span>
                                <strong id="cart-subtotal">$0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Taxes</span>
                                <strong id="cart-taxes">$0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 700;">
                                <span>Total Amount</span>
                                <strong id="cart-total">$0.00</strong>
                            </div>
                            <div style="display: flex; gap: 10px; margin-top: 10px;">
                                <button onclick="handleHoldSale()" class="btn btn-secondary" style="flex-grow: 1; justify-content: center;">Hold Cart</button>
                                <button onclick="openCheckoutModal()" class="btn btn-primary" style="flex-grow: 2; justify-content: center;">Pay & Invoice</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. CATALOG VIEW -->
            <div id="catalog-view" class="view-pane">
                <div style="display: flex; gap: 16px; margin-bottom: 24px;">
                    <button onclick="showProductModal()" class="btn btn-primary">Add New Product</button>
                    <button onclick="showCategoryModal()" class="btn btn-secondary">Categories</button>
                    <button onclick="showBrandModal()" class="btn btn-secondary">Brands</button>
                    <button onclick="showUnitModal()" class="btn btn-secondary">Units</button>
                </div>
                <div class="card">
                    <h3 style="margin-bottom: 16px;">System Catalog Inventory SKUs</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Sku Code</th>
                                    <th>Barcode</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="catalog-products-rows">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 4. INVENTORY VIEW -->
            <div id="inventory-view" class="view-pane">
                <div style="display: flex; gap: 16px; margin-bottom: 24px;">
                    <button onclick="showAdjustStockModal()" class="btn btn-primary">Adjust Stock</button>
                    <button onclick="showTransferStockModal()" class="btn btn-secondary">Transfer Stock</button>
                </div>
                <div class="grid grid-2">
                    <div class="card">
                        <h3 style="margin-bottom: 16px;">Physical Stock counts</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product SKU</th>
                                        <th>Branch</th>
                                        <th>In Stock</th>
                                        <th>Alert Min</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="inventory-stocks-rows">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <h3 style="margin-bottom: 16px;">Movements Audit log</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>SKU</th>
                                        <th>Branch</th>
                                        <th>Change</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody id="inventory-movements-rows">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. PURCHASES VIEW -->
            <div id="purchases-view" class="view-pane">
                <div style="display: flex; gap: 16px; margin-bottom: 24px;">
                    <button onclick="showPurchaseModal()" class="btn btn-primary">Create Purchase Order</button>
                    <button onclick="showSupplierModal()" class="btn btn-secondary">Manage Suppliers</button>
                </div>
                <div class="card">
                    <h3 style="margin-bottom: 16px;">Purchase History Orders</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>PO Number</th>
                                    <th>Supplier</th>
                                    <th>Branch</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="purchases-orders-rows">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 6. CUSTOMERS CRM VIEW -->
            <div id="customers-view" class="view-pane">
                <div style="margin-bottom: 24px;">
                    <button onclick="showCustomerModal()" class="btn btn-primary">Register Customer</button>
                </div>
                <div class="card">
                    <h3 style="margin-bottom: 16px;">Client database CRM</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Client Name</th>
                                    <th>Contact Phone</th>
                                    <th>Client Group</th>
                                    <th>Loyalty Points</th>
                                    <th>Ledger Balance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="crm-customers-rows">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 7. FINANCIALS VIEW -->
            <div id="finance-view" class="view-pane">
                <div style="display: flex; gap: 16px; margin-bottom: 24px;">
                    <button id="open-register-btn" onclick="showOpenDrawerModal()" class="btn btn-primary">Open Drawer Shift</button>
                    <button id="close-register-btn" onclick="showCloseDrawerModal()" class="btn btn-danger" style="display: none;">Close Drawer Shift</button>
                    <button onclick="showExpenseModal()" class="btn btn-secondary">Record Expense</button>
                </div>

                <div class="grid grid-3">
                    <div class="card">
                        <h3 style="margin-bottom: 16px;">Operating Expenses</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="finance-expense-rows">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <h3 style="margin-bottom: 16px;">Quick Profit & Loss (P&L)</h3>
                        <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 10px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span>Gross Revenue</span>
                                <span id="pl-gross-revenue" style="font-weight: 600;">$0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Cost of Goods Sold (COGS)</span>
                                <span id="pl-cogs" style="color: var(--danger);">- $0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-weight: 600; border-top: 1px solid var(--border-color); padding-top: 10px;">
                                <span>Gross Profit</span>
                                <span id="pl-gross-profit">$0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Operating Expenses</span>
                                <span id="pl-expenses" style="color: var(--danger);">- $0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 700; border-top: 1px solid var(--border-color); padding-top: 10px;">
                                <span>Net Profit</span>
                                <span id="pl-net-profit" style="color: var(--success);">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 8. EMPLOYEES HRM VIEW -->
            <div id="employees-view" class="view-pane">
                <div style="margin-bottom: 24px;">
                    <button onclick="showEmployeeModal()" class="btn btn-primary">Hire Staff Employee</button>
                </div>
                <div class="card">
                    <h3 style="margin-bottom: 16px;">Operational employee profiles</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Staff Name</th>
                                    <th>Email ID</th>
                                    <th>Assigned Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="hrm-employees-rows">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 9. ANALYTICAL REPORTS VIEW -->
            <div id="reports-view" class="view-pane">
                <div class="grid grid-2">
                    <div class="card">
                        <h3>Sales Revenue aggregate</h3>
                        <canvas id="salesReportChart" style="max-height: 280px; margin-top: 20px;"></canvas>
                    </div>
                    <div class="card">
                        <h3>Operating Expenses outlay</h3>
                        <canvas id="expenseReportChart" style="max-height: 280px; margin-top: 20px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- 10. SETTINGS VIEW -->
            <div id="settings-view" class="view-pane">
                <div class="grid grid-2">
                    <div class="card">
                        <h3 style="margin-bottom: 20px;">Branch Outlets</h3>
                        <div style="margin-bottom: 16px;">
                            <button onclick="showBranchModal()" class="btn btn-primary">Add Branch Outlet</button>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Branch Name</th>
                                        <th>Address</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="settings-branches-rows">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <h3 style="margin-bottom: 20px;">System Preferences</h3>
                        <form id="settings-form">
                            <div class="form-group">
                                <label for="set-currency-code">Currency Code</label>
                                <input type="text" id="set-currency-code" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="set-currency-symbol">Currency Symbol</label>
                                <input type="text" id="set-currency-symbol" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="set-tax-percent">Tax default (%)</label>
                                <input type="number" step="0.01" id="set-tax-percent" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Configs</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 11. SUPER ADMIN VIEW -->
            <div id="superadmin-view" class="view-pane">
                <div class="card">
                    <h3 style="margin-bottom: 16px;">SaaS Storefront Tenant registrations</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Shop Name</th>
                                    <th>Owner</th>
                                    <th>Email</th>
                                    <th>Subscription</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="saas-shops-rows">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ================= MODALS REGISTRY ================= -->
    
    <!-- A. SHOP REGISTRATION MODAL -->
    <div id="register-modal" class="modal">
        <div class="modal-content">
            <h3>Register New SaaS storefront</h3>
            <form id="register-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Store name</label>
                    <input type="text" id="reg-shop-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Owner Full Name</label>
                    <input type="text" id="reg-owner-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="reg-email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" id="reg-phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Corporate Address</label>
                    <textarea id="reg-address" class="form-control" rows="2" required></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('register-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- B. OPEN DRAWER SHIFT MODAL -->
    <div id="open-drawer-modal" class="modal">
        <div class="modal-content">
            <h3>Open Cash Drawer register</h3>
            <form id="open-drawer-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Select Work Branch</label>
                    <select id="open-drawer-branch" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Opening balance cash drawer</label>
                    <input type="number" step="0.01" id="open-drawer-balance" class="form-control" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('open-drawer-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Shift</button>
                </div>
            </form>
        </div>
    </div>

    <!-- C. CLOSE DRAWER SHIFT MODAL -->
    <div id="close-drawer-modal" class="modal">
        <div class="modal-content">
            <h3>Close Cash Drawer shift</h3>
            <form id="close-drawer-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Closing balance cash drawer (Actual Counted)</label>
                    <input type="number" step="0.01" id="close-drawer-balance" class="form-control" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('close-drawer-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-danger">End Shift</button>
                </div>
            </form>
        </div>
    </div>

    <!-- D. CHECKOUT PAYMENTS MODAL -->
    <div id="checkout-modal" class="modal">
        <div class="modal-content">
            <h3>POS Checkout - Process Invoice</h3>
            <form id="checkout-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Select customer CRM profile</label>
                    <select id="checkout-customer" class="form-control"></select>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select id="checkout-method" class="form-control" onchange="toggleSplitPaymentLayout()" required>
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Split">Split Payments</option>
                    </select>
                </div>
                <div id="split-payments-container" style="display: none; background: rgba(0,0,0,0.1); padding: 12px; border-radius: 12px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Cash Amount</label>
                        <input type="number" step="0.01" id="checkout-split-cash" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label>Card/Other Amount</label>
                        <input type="number" step="0.01" id="checkout-split-other" class="form-control" value="0">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('checkout-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Process Checkout</button>
                </div>
            </form>
        </div>
    </div>

    <!-- E. PRODUCT CREATION MODAL -->
    <div id="product-modal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <h3>Create Product Catalog entry</h3>
            <form id="product-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="prod-name" class="form-control" required>
                </div>
                <div class="grid grid-3" style="gap: 12px;">
                    <div class="form-group">
                        <label>Category</label>
                        <select id="prod-category" class="form-control" required></select>
                    </div>
                    <div class="form-group">
                        <label>Brand</label>
                        <select id="prod-brand" class="form-control" required></select>
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <select id="prod-unit" class="form-control" required></select>
                    </div>
                </div>
                <div class="grid grid-2" style="gap: 12px;">
                    <div class="form-group">
                        <label>Variant SKU Code</label>
                        <input type="text" id="prod-sku" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Barcode ID</label>
                        <input type="text" id="prod-barcode" class="form-control" required>
                    </div>
                </div>
                <div class="grid grid-3" style="gap: 12px;">
                    <div class="form-group">
                        <label>Cost Price</label>
                        <input type="number" step="0.01" id="prod-cost" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Selling Price</label>
                        <input type="number" step="0.01" id="prod-selling" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tax Default (%)</label>
                        <input type="number" step="0.01" id="prod-tax" class="form-control" value="0.00">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeModal('product-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- F. CATEGORY CREATION MODAL -->
    <div id="category-modal" class="modal">
        <div class="modal-content">
            <h3>Add Product Category</h3>
            <form id="category-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Category name</label>
                    <input type="text" id="cat-name" class="form-control" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('category-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- G. BRAND CREATION MODAL -->
    <div id="brand-modal" class="modal">
        <div class="modal-content">
            <h3>Add Product Brand</h3>
            <form id="brand-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Brand name</label>
                    <input type="text" id="brand-name-input" class="form-control" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('brand-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Brand</button>
                </div>
            </form>
        </div>
    </div>

    <!-- H. UNIT CREATION MODAL -->
    <div id="unit-modal" class="modal">
        <div class="modal-content">
            <h3>Add Measurement Unit</h3>
            <form id="unit-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Unit Name</label>
                    <input type="text" id="unit-name-input" class="form-control" placeholder="Kilogram" required>
                </div>
                <div class="form-group">
                    <label>Short Code</label>
                    <input type="text" id="unit-short-input" class="form-control" placeholder="kg" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('unit-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Unit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- I. ADJUST INVENTORY MODAL -->
    <div id="adjust-stock-modal" class="modal">
        <div class="modal-content">
            <h3>Adjust Stock count</h3>
            <form id="adjust-stock-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Select Outlet Branch</label>
                    <select id="adj-branch" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Select Variant SKU</label>
                    <select id="adj-variant" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Adjustment change (e.g. +10 or -5)</label>
                    <input type="number" id="adj-qty" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Reason Type</label>
                    <select id="adj-type" class="form-control" required>
                        <option value="Adjustment">Adjustment</option>
                        <option value="Damage">Damage write-off</option>
                        <option value="StockIn">Stock In</option>
                        <option value="StockOut">Stock Out</option>
                    </select>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('adjust-stock-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply adjustment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- J. TRANSFER INVENTORY MODAL -->
    <div id="transfer-stock-modal" class="modal">
        <div class="modal-content">
            <h3>Transfer stock between outlets</h3>
            <form id="transfer-stock-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Source Branch (From)</label>
                    <select id="trans-from-branch" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Destination Branch (To)</label>
                    <select id="trans-to-branch" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Select Variant SKU</label>
                    <select id="trans-variant" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Transfer quantity</label>
                    <input type="number" id="trans-qty" class="form-control" min="1" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('transfer-stock-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Process Transfer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- K. SUPPLIER CREATION MODAL -->
    <div id="supplier-modal" class="modal">
        <div class="modal-content">
            <h3>Add Supply chain vendor</h3>
            <form id="supplier-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Vendor Business name</label>
                    <input type="text" id="sup-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Contact Person name</label>
                    <input type="text" id="sup-contact" class="form-control">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="sup-email" class="form-control">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" id="sup-phone" class="form-control">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('supplier-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Vendor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- L. PURCHASE CREATION MODAL -->
    <div id="purchase-modal" class="modal">
        <div class="modal-content">
            <h3>Create Purchase Order</h3>
            <form id="purchase-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Select supplier vendor</label>
                    <select id="po-supplier" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Select checkout branch</label>
                    <select id="po-branch" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Select Product Variant</label>
                    <select id="po-variant" class="form-control" required></select>
                </div>
                <div class="grid grid-2" style="gap: 12px;">
                    <div class="form-group">
                        <label>Purchase Quantity</label>
                        <input type="number" id="po-qty" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Unit Cost price</label>
                        <input type="number" step="0.01" id="po-cost" class="form-control" required>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('purchase-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- M. CUSTOMER CREATION MODAL -->
    <div id="customer-modal" class="modal">
        <div class="modal-content">
            <h3>Register Customer profile</h3>
            <form id="customer-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Customer Full Name</label>
                    <input type="text" id="cust-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" id="cust-phone" class="form-control">
                </div>
                <div class="form-group">
                    <label>Email ID</label>
                    <input type="email" id="cust-email" class="form-control">
                </div>
                <div class="form-group">
                    <label>Group categorization</label>
                    <select id="cust-group" class="form-control">
                        <option value="General">General Retail</option>
                        <option value="VIP">VIP Premium Client</option>
                        <option value="Wholesale">Wholesale Trader</option>
                    </select>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('customer-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register CRM</button>
                </div>
            </form>
        </div>
    </div>

    <!-- N. EXPENSE LOGGER MODAL -->
    <div id="expense-modal" class="modal">
        <div class="modal-content">
            <h3>Record branch expenses</h3>
            <form id="expense-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Select branch outlet</label>
                    <select id="exp-branch" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Expense title name</label>
                    <input type="text" id="exp-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Expense amount</label>
                    <input type="number" step="0.01" id="exp-amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category type</label>
                    <select id="exp-cat" class="form-control" required>
                        <option value="Rent">Rent lease</option>
                        <option value="Utilities">Utilities billings</option>
                        <option value="Salaries">Payroll salaries</option>
                        <option value="Marketing">Marketing campaigns</option>
                        <option value="Other">Other operations</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Billing date</label>
                    <input type="date" id="exp-date" class="form-control" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('expense-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record ledger</button>
                </div>
            </form>
        </div>
    </div>

    <!-- O. EMPLOYEE MODAL -->
    <div id="employee-modal" class="modal">
        <div class="modal-content">
            <h3>Hire Employee staff</h3>
            <form id="employee-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Staff Full Name</label>
                    <input type="text" id="emp-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="emp-email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password Account credentials</label>
                    <input type="password" id="emp-pass" class="form-control" placeholder="6+ characters" required>
                </div>
                <div class="form-group">
                    <label>Assigned Work Outlet</label>
                    <select id="emp-branch" class="form-control"></select>
                </div>
                <div class="form-group">
                    <label>Staff Permission role</label>
                    <select id="emp-role" class="form-control" required>
                        <option value="Cashier">Cashier operator</option>
                        <option value="Manager">Branch manager</option>
                        <option value="Inventory Staff">Inventory supervisor</option>
                    </select>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('employee-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Process hire</button>
                </div>
            </form>
        </div>
    </div>

    <!-- P. BRANCH CREATION MODAL -->
    <div id="branch-modal" class="modal">
        <div class="modal-content">
            <h3>Create Outlet Branch</h3>
            <form id="branch-form" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Branch Name</label>
                    <input type="text" id="br-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" id="br-phone" class="form-control">
                </div>
                <div class="form-group">
                    <label>Address location</label>
                    <textarea id="br-address" class="form-control" rows="2" required></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('branch-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Outlet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Container for notifications -->
    <div id="notification-box"></div>

    <!-- ================= SYSTEM JAVASCRIPT CONTROLLERS ================= -->
    <script>
        // Set CSRF token configuration globally in fetch calls
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // State managers
        let currentUser = null;
        let activeView = 'dashboard-view';
        let cart = [];
        let systemSettings = {};
        
        let categories = [];
        let brands = [];
        let units = [];
        let products = [];
        let branches = [];
        let suppliers = [];
        let customers = [];
        let registerStatus = { is_open: false, register: null };

        // Analytical charts
        let salesChart = null;
        let expenseChart = null;

        // Initialize session on load
        window.addEventListener('DOMContentLoaded', () => {
            checkAuth();
            setupEventListeners();
        });

        // Check authentication state
        async function checkAuth() {
            try {
                const response = await fetch('/auth/me');
                if (response.ok) {
                    const data = await response.json();
                    if (data.user) {
                        currentUser = data.user;
                        document.getElementById('auth-screen').style.display = 'none';
                        document.getElementById('app-layout').style.display = 'flex';
                        
                        // Populate profiles sidebar info
                        document.getElementById('user-display-name').innerText = currentUser.name;
                        document.getElementById('user-display-branch').innerText = currentUser.branch ? currentUser.branch.name : 'Super Administrator';
                        
                        // Show superadmin links if user is Super Admin
                        if (currentUser.roles.includes('Super Admin')) {
                            document.querySelectorAll('.superadmin-link').forEach(el => el.style.display = 'block');
                        }

                        // Set theme preference
                        document.documentElement.setAttribute('data-theme', 'light');

                        // Bootstrap the active workstation context
                        bootstrapSystem();
                    } else {
                        showLoginScreen();
                    }
                } else {
                    showLoginScreen();
                }
            } catch (err) {
                showLoginScreen();
            }
        }

        function showLoginScreen() {
            document.getElementById('auth-screen').style.display = 'flex';
            document.getElementById('app-layout').style.display = 'none';
        }

        // Setup sidebar click toggles and forms submissions
        function setupEventListeners() {
            // Sidebar buttons
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    const viewId = item.getAttribute('data-view');
                    switchView(viewId);
                });
            });

            // Login submission
            document.getElementById('login-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('login-email').value;
                const password = document.getElementById('login-password').value;

                try {
                    const res = await fetch('/auth/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ email, password })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showToast('Authenticated successfully.', 'success');
                        checkAuth();
                    } else {
                        showToast(data.error || 'Authentication failed.', 'danger');
                    }
                } catch (err) {
                    showToast('Network error during login process.', 'danger');
                }
            });

            // SaaS vendor registration
            document.getElementById('register-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const shop_name = document.getElementById('reg-shop-name').value;
                const owner_name = document.getElementById('reg-owner-name').value;
                const email = document.getElementById('reg-email').value;
                const phone = document.getElementById('reg-phone').value;
                const address = document.getElementById('reg-address').value;

                try {
                    const res = await fetch('/saas/register', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ shop_name, owner_name, email, phone, address })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showToast(data.message, 'success');
                        closeModal('register-modal');
                    } else {
                        showToast(data.error || 'Registration failed.', 'danger');
                    }
                } catch (err) {
                    showToast('Network error during registration.', 'danger');
                }
            });

            // Cash register open drawer shift
            document.getElementById('open-drawer-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const branch_id = document.getElementById('open-drawer-branch').value;
                const opening_balance = document.getElementById('open-drawer-balance').value;

                try {
                    const res = await fetch('/api/financial/register/open', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ branch_id, opening_balance })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showToast(data.message, 'success');
                        closeModal('open-drawer-modal');
                        checkDrawerStatus();
                    } else {
                        showToast(data.error || 'Failed to open register.', 'danger');
                    }
                } catch (err) {
                    showToast('Server connection error.', 'danger');
                }
            });

            // Cash register close shift
            document.getElementById('close-drawer-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const closing_balance = document.getElementById('close-drawer-balance').value;

                try {
                    const res = await fetch('/api/financial/register/close', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ closing_balance })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showToast(`Drawer closed. Variance: $${data.variance}`, 'success');
                        closeModal('close-drawer-modal');
                        checkDrawerStatus();
                    } else {
                        showToast(data.error || 'Failed to close register.', 'danger');
                    }
                } catch (err) {
                    showToast('Server connection error.', 'danger');
                }
            });

            // Category creator
            document.getElementById('category-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = document.getElementById('cat-name').value;
                try {
                    const res = await fetch('/api/catalog/categories', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ name })
                    });
                    if (res.ok) {
                        showToast('Category created.', 'success');
                        closeModal('category-modal');
                        fetchCatalogData();
                    }
                } catch (err) {}
            });

            // Brand creator
            document.getElementById('brand-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = document.getElementById('brand-name-input').value;
                try {
                    const res = await fetch('/api/catalog/brands', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ name })
                    });
                    if (res.ok) {
                        showToast('Brand created.', 'success');
                        closeModal('brand-modal');
                        fetchCatalogData();
                    }
                } catch (err) {}
            });

            // Unit creator
            document.getElementById('unit-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = document.getElementById('unit-name-input').value;
                const short_name = document.getElementById('unit-short-input').value;
                try {
                    const res = await fetch('/api/catalog/units', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ name, short_name })
                    });
                    if (res.ok) {
                        showToast('Unit created.', 'success');
                        closeModal('unit-modal');
                        fetchCatalogData();
                    }
                } catch (err) {}
            });

            // Product creator
            document.getElementById('product-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    name: document.getElementById('prod-name').value,
                    category_id: document.getElementById('prod-category').value,
                    brand_id: document.getElementById('prod-brand').value,
                    unit_id: document.getElementById('prod-unit').value,
                    has_variants: false,
                    variants: [{
                        variant_name: 'Standard',
                        sku: document.getElementById('prod-sku').value,
                        barcode: document.getElementById('prod-barcode').value,
                        cost_price: document.getElementById('prod-cost').value,
                        selling_price: document.getElementById('prod-selling').value,
                        tax_percentage: document.getElementById('prod-tax').value,
                    }]
                };

                try {
                    const res = await fetch('/api/catalog/products', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showToast('Product added successfully.', 'success');
                        closeModal('product-modal');
                        fetchCatalogData();
                    } else {
                        showToast(data.error || 'Failed to create product.', 'danger');
                    }
                } catch (err) {}
            });

            // Adjust Stock submission
            document.getElementById('adjust-stock-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    branch_id: document.getElementById('adj-branch').value,
                    product_variant_id: document.getElementById('adj-variant').value,
                    quantity: document.getElementById('adj-qty').value,
                    type: document.getElementById('adj-type').value,
                };
                try {
                    const res = await fetch('/api/inventory/adjust', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showToast(data.message, 'success');
                        closeModal('adjust-stock-modal');
                        fetchInventoryData();
                    } else {
                        showToast(data.error || 'Failed to adjust stock.', 'danger');
                    }
                } catch (err) {}
            });

            // Transfer Stock submission
            document.getElementById('transfer-stock-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    from_branch_id: document.getElementById('trans-from-branch').value,
                    to_branch_id: document.getElementById('trans-to-branch').value,
                    product_variant_id: document.getElementById('trans-variant').value,
                    quantity: document.getElementById('trans-qty').value,
                };
                try {
                    const res = await fetch('/api/inventory/transfer', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showToast(data.message, 'success');
                        closeModal('transfer-stock-modal');
                        fetchInventoryData();
                    } else {
                        showToast(data.error || 'Failed to transfer stock.', 'danger');
                    }
                } catch (err) {}
            });

            // Supplier vendor creation
            document.getElementById('supplier-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    name: document.getElementById('sup-name').value,
                    contact_name: document.getElementById('sup-contact').value,
                    email: document.getElementById('sup-email').value,
                    phone: document.getElementById('sup-phone').value,
                };
                try {
                    const res = await fetch('/api/suppliers', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        showToast('Supplier registered.', 'success');
                        closeModal('supplier-modal');
                        fetchPurchasesData();
                    }
                } catch (err) {}
            });

            // Purchase order placement
            document.getElementById('purchase-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    supplier_id: document.getElementById('po-supplier').value,
                    branch_id: document.getElementById('po-branch').value,
                    items: [{
                        product_variant_id: document.getElementById('po-variant').value,
                        quantity: document.getElementById('po-qty').value,
                        cost_price: document.getElementById('po-cost').value,
                    }]
                };
                try {
                    const res = await fetch('/api/purchases', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        showToast('Purchase Order placed.', 'success');
                        closeModal('purchase-modal');
                        fetchPurchasesData();
                    }
                } catch (err) {}
            });

            // Customer CRM creation
            document.getElementById('customer-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    name: document.getElementById('cust-name').value,
                    phone: document.getElementById('cust-phone').value,
                    email: document.getElementById('cust-email').value,
                    customer_group: document.getElementById('cust-group').value,
                };
                try {
                    const res = await fetch('/api/customers', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        showToast('Customer CRM registered.', 'success');
                        closeModal('customer-modal');
                        fetchCRMData();
                    }
                } catch (err) {}
            });

            // Expense log submission
            document.getElementById('expense-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    branch_id: document.getElementById('exp-branch').value,
                    name: document.getElementById('exp-name').value,
                    amount: document.getElementById('exp-amount').value,
                    category: document.getElementById('exp-cat').value,
                    date: document.getElementById('exp-date').value,
                };
                try {
                    const res = await fetch('/api/financial/expenses', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        showToast('Expense logged.', 'success');
                        closeModal('expense-modal');
                        fetchFinanceData();
                    }
                } catch (err) {}
            });

            // Employee hire submission
            document.getElementById('employee-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    name: document.getElementById('emp-name').value,
                    email: document.getElementById('emp-email').value,
                    password: document.getElementById('emp-pass').value,
                    branch_id: document.getElementById('emp-branch').value || null,
                    role: document.getElementById('emp-role').value,
                };
                try {
                    const res = await fetch('/api/employees', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showToast('Staff hired successfully.', 'success');
                        closeModal('employee-modal');
                        fetchHRMData();
                    } else {
                        showToast(data.error || 'Failed to hire staff.', 'danger');
                    }
                } catch (err) {}
            });

            // Branch creation submission
            document.getElementById('branch-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    name: document.getElementById('br-name').value,
                    phone: document.getElementById('br-phone').value,
                    address: document.getElementById('br-address').value,
                };
                try {
                    const res = await fetch('/api/settings/branches', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showToast('Outlet branch registered.', 'success');
                        closeModal('branch-modal');
                        fetchSettingsData();
                    } else {
                        showToast(data.error || 'Failed to create branch.', 'danger');
                    }
                } catch (err) {}
            });

            // Checkout POS checkout submission
            document.getElementById('checkout-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const customer_id = document.getElementById('checkout-customer').value || null;
                const method = document.getElementById('checkout-method').value;
                
                let payments = [];
                if (method === 'Split') {
                    const cash = parseFloat(document.getElementById('checkout-split-cash').value) || 0;
                    const other = parseFloat(document.getElementById('checkout-split-other').value) || 0;
                    payments.push({ amount: cash, payment_method: 'Cash' });
                    payments.push({ amount: other, payment_method: 'Card' });
                } else {
                    const total = cart.reduce((acc, item) => acc + (item.quantity * item.price), 0);
                    payments.push({ amount: total, payment_method: method });
                }

                const payload = {
                    branch_id: currentUser.branch_id || branches[0].id,
                    customer_id: customer_id,
                    hold_status: 'completed',
                    items: cart.map(item => ({
                        product_variant_id: item.variant_id,
                        quantity: item.quantity,
                        unit_price: item.price,
                        tax_amount: 0,
                        discount_amount: 0
                    })),
                    payments: payments
                };

                try {
                    const res = await fetch('/api/pos/checkout', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showToast('Invoice generated successfully.', 'success');
                        closeModal('checkout-modal');
                        cart = [];
                        renderCart();
                        switchView('dashboard-view');
                    } else {
                        showToast(data.error || 'Checkout failed.', 'danger');
                    }
                } catch (err) {}
            });

            // Search product in POS screen by typing
            document.getElementById('pos-search-input').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    lookupProductPOS(document.getElementById('pos-search-input').value);
                }
            });
        }

        // Logout context
        async function handleLogout() {
            try {
                await fetch('/auth/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                currentUser = null;
                showToast('Logged out of system.', 'info');
                showLoginScreen();
            } catch (err) {}
        }

        // View tabs controller
        function switchView(viewId) {
            document.querySelectorAll('.view-pane').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            
            const targetPane = document.getElementById(viewId);
            if (targetPane) {
                targetPane.classList.add('active');
            }

            const navItem = document.querySelector(`.nav-item[data-view="${viewId}"]`);
            if (navItem) {
                navItem.classList.add('active');
            }

            // Set Title text
            const titlesMap = {
                'dashboard-view': 'Dashboard Overview',
                'pos-view': 'POS Cashier Terminal',
                'catalog-view': 'Product Catalog Management',
                'inventory-view': 'Inventory & stock sheets',
                'purchases-view': 'Supply Procurement purchases',
                'customers-view': 'CRM Customer management',
                'finance-view': 'Shift registers & accounting',
                'employees-view': 'Staff & HRM Management',
                'reports-view': 'Analytical reporting',
                'settings-view': 'General settings and configurations',
                'superadmin-view': 'SaaS System Landlord Dashboard'
            };
            document.getElementById('view-title').innerText = titlesMap[viewId] || 'System Console';

            activeView = viewId;

            // Trigger specific page fetches
            if (viewId === 'dashboard-view') fetchDashboardData();
            if (viewId === 'pos-view') fetchPOSItems();
            if (viewId === 'catalog-view') fetchCatalogData();
            if (viewId === 'inventory-view') fetchInventoryData();
            if (viewId === 'purchases-view') fetchPurchasesData();
            if (viewId === 'customers-view') fetchCRMData();
            if (viewId === 'finance-view') fetchFinanceData();
            if (viewId === 'employees-view') fetchHRMData();
            if (viewId === 'reports-view') fetchReportsData();
            if (viewId === 'settings-view') fetchSettingsData();
            if (viewId === 'superadmin-view') fetchSaaSShops();
        }

        // Dynamic theme toggle
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            document.documentElement.setAttribute('data-theme', current === 'dark' ? 'light' : 'dark');
        }

        // Open/Close Modals helper
        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }

        // Alert toasts helper
        function showToast(message, type = 'info') {
            const box = document.getElementById('notification-box');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerText = message;
            box.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ================= DATA BOOTSTRAPPER =================
        async function bootstrapSystem() {
            // Load base list data like branches, register status, customer listings
            try {
                const resBranch = await fetch('/api/settings/branches');
                if (resBranch.ok) branches = await resBranch.ok ? await resBranch.json() : [];

                checkDrawerStatus();
                fetchDashboardData();
            } catch (err) {}
        }

        async function checkDrawerStatus() {
            try {
                const res = await fetch('/api/financial/register/status');
                if (res.ok) {
                    const data = await res.json();
                    registerStatus = data;
                    const chip = document.getElementById('drawer-status-chip');
                    if (data.is_open) {
                        chip.className = 'badge badge-success';
                        chip.innerText = `Shift Drawer: Open (Branch ${data.register.branch_id})`;
                        document.getElementById('open-register-btn').style.display = 'none';
                        document.getElementById('close-register-btn').style.display = 'inline-flex';
                    } else {
                        chip.className = 'badge badge-danger';
                        chip.innerText = 'Shift Drawer: Closed';
                        document.getElementById('open-register-btn').style.display = 'inline-flex';
                        document.getElementById('close-register-btn').style.display = 'none';
                    }
                }
            } catch (err) {}
        }

        // ================= VIEW DATA LOADERS =================

        // 1. DASHBOARD LOAD
        async function fetchDashboardData() {
            try {
                const res = await fetch('/api/dashboard');
                if (res.ok) {
                    const data = await res.json();
                    document.getElementById('dash-daily-sales').innerText = `$${parseFloat(data.daily_sales || 0).toFixed(2)}`;
                    document.getElementById('dash-monthly-sales').innerText = `$${parseFloat(data.monthly_sales || 0).toFixed(2)}`;
                    document.getElementById('dash-purchase-outlays').innerText = `$${parseFloat(data.purchase_outlays || 0).toFixed(2)}`;
                    document.getElementById('dash-stock-quantity').innerText = `${data.stock_quantity || 0} pcs`;

                    // Render Recent transactions
                    const tableSales = document.getElementById('dash-recent-sales-rows');
                    tableSales.innerHTML = data.recent_transactions.map(sale => `
                        <tr>
                            <td><strong>${sale.invoice_number}</strong></td>
                            <td>${sale.branch ? sale.branch.name : 'N/A'}</td>
                            <td>${sale.customer ? sale.customer.name : 'Anonymous'}</td>
                            <td>$${parseFloat(sale.total_amount).toFixed(2)}</td>
                            <td><span class="badge ${sale.payment_status === 'Paid' ? 'badge-success' : 'badge-warning'}">${sale.payment_status}</span></td>
                        </tr>
                    `).join('') || '<tr><td colspan="5" style="text-align: center;">No transactions logged yet.</td></tr>';

                    // Render Top Products
                    const tableProd = document.getElementById('dash-top-products-rows');
                    tableProd.innerHTML = data.top_products.map(item => `
                        <tr>
                            <td>${item.product_variant.product.name} (${item.product_variant.variant_name})</td>
                            <td>${item.total_sold} pcs</td>
                            <td>$${parseFloat(item.total_revenue).toFixed(2)}</td>
                        </tr>
                    `).join('') || '<tr><td colspan="3" style="text-align: center;">No sales records.</td></tr>';
                }
            } catch (err) {}
        }

        // 2. POS SCREEN CONTROLLERS
        async function fetchPOSItems() {
            try {
                const res = await fetch('/api/catalog/products');
                if (res.ok) {
                    products = await res.json();
                    const grid = document.getElementById('pos-item-grid');
                    grid.innerHTML = products.map(prod => {
                        return prod.variants.map(v => `
                            <div class="pos-product-card" onclick='addToCart(${v.id}, "${prod.name}", ${v.selling_price})'>
                                <h4 style="font-size: 15px; margin-bottom: 4px;">${prod.name}</h4>
                                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">${v.variant_name}</div>
                                <strong style="color: var(--primary);">$${parseFloat(v.selling_price).toFixed(2)}</strong>
                            </div>
                        `).join('');
                    }).join('');
                }
            } catch (err) {}
        }

        function addToCart(variantId, name, price) {
            const existing = cart.find(item => item.variant_id === variantId);
            if (existing) {
                existing.quantity++;
            } else {
                cart.push({ variant_id: variantId, name, price, quantity: 1 });
            }
            showToast(`Added ${name} to cart.`, 'info');
            renderCart();
        }

        function removeFromCart(variantId) {
            cart = cart.filter(item => item.variant_id !== variantId);
            renderCart();
        }

        function changeCartQty(variantId, newQty) {
            const item = cart.find(i => i.variant_id === variantId);
            if (item) {
                item.quantity = parseInt(newQty) || 1;
                renderCart();
            }
        }

        function renderCart() {
            const list = document.getElementById('pos-cart-items');
            if (cart.length === 0) {
                list.innerHTML = `<div style="text-align: center; color: var(--text-secondary); margin-top: 40px;">Cart is empty</div>`;
                document.getElementById('cart-subtotal').innerText = '$0.00';
                document.getElementById('cart-taxes').innerText = '$0.00';
                document.getElementById('cart-total').innerText = '$0.00';
                return;
            }

            list.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div>
                        <div style="font-weight: 600;">${item.name}</div>
                        <div style="font-size: 12px; color: var(--text-secondary);">$${parseFloat(item.price).toFixed(2)} each</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="number" value="${item.quantity}" min="1" onchange="changeCartQty(${item.variant_id}, this.value)" style="width: 50px; padding: 4px; border-radius: 6px; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); color: #fff; text-align: center;">
                        <button onclick="removeFromCart(${item.variant_id})" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">Remove</button>
                    </div>
                </div>
            `).join('');

            const subtotal = cart.reduce((acc, item) => acc + (item.quantity * item.price), 0);
            const taxes = subtotal * 0.05; // 5% mock tax
            const total = subtotal + taxes;

            document.getElementById('cart-subtotal').innerText = `$${subtotal.toFixed(2)}`;
            document.getElementById('cart-taxes').innerText = `$${taxes.toFixed(2)}`;
            document.getElementById('cart-total').innerText = `$${total.toFixed(2)}`;
        }

        async function lookupProductPOS(query) {
            try {
                const res = await fetch('/api/pos/lookup', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ query })
                });
                const variant = await res.json();
                if (res.ok) {
                    addToCart(variant.id, variant.product.name, variant.selling_price);
                    document.getElementById('pos-search-input').value = '';
                } else {
                    showToast('Barcode scanner lookup: Product variant not registered.', 'danger');
                }
            } catch (err) {}
        }

        function simulateBarcodeScan() {
            const barcode = prompt("Type variant barcode (e.g. BAR123) to simulate scanner input:");
            if (barcode) lookupProductPOS(barcode);
        }

        async function openCheckoutModal() {
            if (cart.length === 0) {
                showToast('Your checkout cart is empty.', 'warning');
                return;
            }
            
            // Populate customers listing dropdown
            try {
                const res = await fetch('/api/customers');
                if (res.ok) {
                    const custData = await res.json();
                    const select = document.getElementById('checkout-customer');
                    select.innerHTML = '<option value="">-- Guest Anonymous Customer --</option>' + custData.map(c => `
                        <option value="${c.id}">${c.name} (${c.customer_group})</option>
                    `).join('');
                }
            } catch(e){}

            openModal('checkout-modal');
        }

        function toggleSplitPaymentLayout() {
            const method = document.getElementById('checkout-method').value;
            document.getElementById('split-payments-container').style.display = method === 'Split' ? 'block' : 'none';
        }

        async function handleHoldSale() {
            showToast('Held cart features saved to background holds queue.', 'success');
        }

        // 3. CATALOG LOADERS
        async function fetchCatalogData() {
            try {
                const res = await fetch('/api/catalog/products');
                if (res.ok) {
                    const data = await res.json();
                    const tbody = document.getElementById('catalog-products-rows');
                    tbody.innerHTML = data.map(prod => {
                        return prod.variants.map(v => `
                            <tr>
                                <td><strong>${prod.name}</strong></td>
                                <td>${prod.category ? prod.category.name : 'N/A'}</td>
                                <td>${prod.brand ? prod.brand.name : 'N/A'}</td>
                                <td><code>${v.sku}</code></td>
                                <td>${v.barcode}</td>
                                <td>$${parseFloat(v.selling_price).toFixed(2)}</td>
                                <td>
                                    <button onclick="deleteProduct(${prod.id})" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">Delete</button>
                                </td>
                            </tr>
                        `).join('');
                    }).join('');
                }
            } catch (err) {}
        }

        async function deleteProduct(id) {
            if (confirm("Remove product entry from catalog?")) {
                await fetch(`/api/catalog/products/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
                fetchCatalogData();
            }
        }

        async function showProductModal() {
            // Fetch category/brand/units dropdowns first
            try {
                const catRes = await fetch('/api/catalog/categories');
                const brandRes = await fetch('/api/catalog/brands');
                const unitRes = await fetch('/api/catalog/units');

                const cats = await catRes.json();
                const brands = await brandRes.json();
                const units = await unitRes.json();

                document.getElementById('prod-category').innerHTML = cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
                document.getElementById('prod-brand').innerHTML = '<option value="">None</option>' + brands.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
                document.getElementById('prod-unit').innerHTML = units.map(u => `<option value="${u.id}">${u.name}</option>`).join('');
                
                openModal('product-modal');
            } catch (err) {}
        }

        function showCategoryModal() { openModal('category-modal'); }
        function showBrandModal() { openModal('brand-modal'); }
        function showUnitModal() { openModal('unit-modal'); }

        // 4. INVENTORY LOADERS
        async function fetchInventoryData() {
            try {
                const resStock = await fetch('/api/inventory/stock');
                const resMove = await fetch('/api/inventory/movements');

                const stockData = await resStock.json();
                const moveData = await resMove.json();

                document.getElementById('inventory-stocks-rows').innerHTML = stockData.map(st => `
                    <tr>
                        <td><code>${st.product_variant.sku}</code></td>
                        <td>${st.branch ? st.branch.name : 'N/A'}</td>
                        <td><strong>${st.quantity} pcs</strong></td>
                        <td>${st.low_stock_alert}</td>
                        <td><span class="badge ${st.quantity <= st.low_stock_alert ? 'badge-danger' : 'badge-success'}">${st.quantity <= st.low_stock_alert ? 'Low Stock' : 'Good'}</span></td>
                    </tr>
                `).join('') || '<tr><td colspan="5" style="text-align: center;">No stock counts found.</td></tr>';

                document.getElementById('inventory-movements-rows').innerHTML = moveData.map(m => `
                    <tr>
                        <td>${new Date(m.created_at).toLocaleDateString()}</td>
                        <td><code>${m.product_variant.sku}</code></td>
                        <td>${m.branch ? m.branch.name : 'N/A'}</td>
                        <td><strong style="color: ${m.quantity > 0 ? 'var(--success)' : 'var(--danger)'};">${m.quantity > 0 ? '+' : ''}${m.quantity}</strong></td>
                        <td><span class="badge badge-warning">${m.type}</span></td>
                    </tr>
                `).join('') || '<tr><td colspan="5" style="text-align: center;">No movements recorded.</td></tr>';
            } catch (err) {}
        }

        async function showAdjustStockModal() {
            const resBranch = await fetch('/api/settings/branches');
            const resProd = await fetch('/api/catalog/products');

            const branches = await resBranch.json();
            const products = await resProd.json();

            document.getElementById('adj-branch').innerHTML = branches.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
            
            let variantsHTML = '';
            products.forEach(p => {
                p.variants.forEach(v => {
                    variantsHTML += `<option value="${v.id}">${p.name} (${v.sku})</option>`;
                });
            });
            document.getElementById('adj-variant').innerHTML = variantsHTML;

            openModal('adjust-stock-modal');
        }

        async function showTransferStockModal() {
            const resBranch = await fetch('/api/settings/branches');
            const resProd = await fetch('/api/catalog/products');

            const branches = await resBranch.json();
            const products = await resProd.json();

            const optionsBranch = branches.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
            document.getElementById('trans-from-branch').innerHTML = optionsBranch;
            document.getElementById('trans-to-branch').innerHTML = optionsBranch;

            let variantsHTML = '';
            products.forEach(p => {
                p.variants.forEach(v => {
                    variantsHTML += `<option value="${v.id}">${p.name} (${v.sku})</option>`;
                });
            });
            document.getElementById('trans-variant').innerHTML = variantsHTML;

            openModal('transfer-stock-modal');
        }

        // 5. PURCHASES LOADERS
        async function fetchPurchasesData() {
            try {
                const res = await fetch('/api/purchases');
                const pData = await res.json();
                document.getElementById('purchases-orders-rows').innerHTML = pData.map(p => `
                    <tr>
                        <td><strong>${p.purchase_number}</strong></td>
                        <td>${p.supplier.name}</td>
                        <td>${p.branch.name}</td>
                        <td>$${parseFloat(p.total_amount).toFixed(2)}</td>
                        <td><span class="badge ${p.status === 'received' ? 'badge-success' : 'badge-warning'}">${p.status}</span></td>
                        <td>
                            ${p.status === 'pending' ? `<button onclick="approvePO(${p.id})" class="btn btn-primary" style="padding: 4px 8px; font-size: 11px;">Approve</button>` : ''}
                            ${p.status === 'approved' ? `<button onclick="receivePO(${p.id})" class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px;">GRN Receive</button>` : ''}
                            ${p.status === 'received' ? `<span style="font-size:12px; color: var(--text-secondary);">Processed</span>` : ''}
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="6" style="text-align: center;">No purchase orders placed yet.</td></tr>';
            } catch (err) {}
        }

        async function approvePO(id) {
            await fetch(`/api/purchases/${id}/approve`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
            showToast('PO Approved.', 'success');
            fetchPurchasesData();
        }

        async function receivePO(id) {
            await fetch(`/api/purchases/${id}/receive`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
            showToast('PO Received into stock levels.', 'success');
            fetchPurchasesData();
        }

        async function showPurchaseModal() {
            const resSup = await fetch('/api/suppliers');
            const resBranch = await fetch('/api/settings/branches');
            const resProd = await fetch('/api/catalog/products');

            const sups = await resSup.json();
            const branches = await resBranch.json();
            const products = await resProd.json();

            document.getElementById('po-supplier').innerHTML = sups.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
            document.getElementById('po-branch').innerHTML = branches.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
            
            let variantsHTML = '';
            products.forEach(p => {
                p.variants.forEach(v => {
                    variantsHTML += `<option value="${v.id}">${p.name} (${v.sku})</option>`;
                });
            });
            document.getElementById('po-variant').innerHTML = variantsHTML;

            openModal('purchase-modal');
        }

        function showSupplierModal() { openModal('supplier-modal'); }

        // 6. CRM CUSTOMERS LOAD
        async function fetchCRMData() {
            try {
                const res = await fetch('/api/customers');
                const cData = await res.json();
                document.getElementById('crm-customers-rows').innerHTML = cData.map(c => `
                    <tr>
                        <td><strong>${c.name}</strong></td>
                        <td>${c.phone || 'N/A'}</td>
                        <td>${c.customer_group}</td>
                        <td>${c.loyalty_points} pts</td>
                        <td>$${parseFloat(c.balance).toFixed(2)}</td>
                        <td>
                            <button onclick="viewCustomerLedger(${c.id})" class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px;">Ledger</button>
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="6" style="text-align: center;">No customers CRM listings.</td></tr>';
            } catch (err) {}
        }

        function showCustomerModal() { openModal('customer-modal'); }

        function viewCustomerLedger(id) {
            showToast('Fetching CRM sales ledger for customer.', 'info');
        }

        // 7. FINANCE & Drawer shifts Load
        async function fetchFinanceData() {
            try {
                const resExp = await fetch('/api/financial/expenses');
                const resPL = await fetch('/api/financial/pl');

                const expData = await resExp.json();
                const plData = await resPL.json();

                // Expenses list
                document.getElementById('finance-expense-rows').innerHTML = expData.map(e => `
                    <tr>
                        <td>${e.date}</td>
                        <td>${e.name}</td>
                        <td>${e.category}</td>
                        <td>$${parseFloat(e.amount).toFixed(2)}</td>
                    </tr>
                `).join('') || '<tr><td colspan="4" style="text-align: center;">No expenses recorded.</td></tr>';

                // Profit Loss Summary
                document.getElementById('pl-gross-revenue').innerText = `$${parseFloat(plData.gross_revenue).toFixed(2)}`;
                document.getElementById('pl-cogs').innerText = `- $${parseFloat(plData.cost_of_goods_sold).toFixed(2)}`;
                document.getElementById('pl-gross-profit').innerText = `$${parseFloat(plData.gross_profit).toFixed(2)}`;
                document.getElementById('pl-expenses').innerText = `- $${parseFloat(plData.operational_expenses).toFixed(2)}`;
                
                const net = plData.net_profit;
                const plNet = document.getElementById('pl-net-profit');
                plNet.innerText = (net >= 0 ? '' : '-') + `$${Math.abs(net).toFixed(2)}`;
                plNet.style.color = net >= 0 ? 'var(--success)' : 'var(--danger)';
            } catch (err) {}
        }

        async function showOpenDrawerModal() {
            // Populate branch options
            const select = document.getElementById('open-drawer-branch');
            select.innerHTML = branches.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
            openModal('open-drawer-modal');
        }

        function showCloseDrawerModal() {
            openModal('close-drawer-modal');
        }

        function showExpenseModal() {
            const select = document.getElementById('exp-branch');
            select.innerHTML = branches.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
            
            // Set date input to today
            document.getElementById('exp-date').value = new Date().toISOString().split('T')[0];
            openModal('expense-modal');
        }

        // 8. HRM EMPLOYEES LOAD
        async function fetchHRMData() {
            try {
                const res = await fetch('/api/employees');
                const eData = await res.json();
                document.getElementById('hrm-employees-rows').innerHTML = eData.map(e => `
                    <tr>
                        <td><strong>${e.name}</strong></td>
                        <td>${e.email}</td>
                        <td><span class="badge badge-success">${e.roles.map(r => r.name).join(', ') || 'Staff'}</span></td>
                        <td>
                            <button onclick="deactivateEmployee(${e.id})" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">Terminate</button>
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="4" style="text-align: center;">No employees hired.</td></tr>';
            } catch (err) {}
        }

        async function showEmployeeModal() {
            // Populate branches options
            document.getElementById('emp-branch').innerHTML = '<option value="">Super Admin (HQ)</option>' + branches.map(b => `
                <option value="${b.id}">${b.name}</option>
            `).join('');
            openModal('employee-modal');
        }

        async function deactivateEmployee(id) {
            if (confirm("Terminate staff profile?")) {
                const res = await fetch(`/api/employees/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
                const data = await res.json();
                if (res.ok) {
                    showToast(data.message, 'success');
                    fetchHRMData();
                } else {
                    showToast(data.error || 'Failed to remove staff.', 'danger');
                }
            }
        }

        // 9. ANALYTICS & CHARTS
        async function fetchReportsData() {
            try {
                const resSales = await fetch('/api/reports/sales?group_by=day');
                const resFinance = await fetch('/api/reports/financial');

                const salesData = await resSales.json();
                const financeData = await resFinance.json();

                // Destory existing charts to rebuild
                if (salesChart) salesChart.destroy();
                if (expenseChart) expenseChart.destroy();

                // Render Sales Chart
                const ctxSales = document.getElementById('salesReportChart').getContext('2d');
                salesChart = new Chart(ctxSales, {
                    type: 'line',
                    data: {
                        labels: salesData.map(d => d.period),
                        datasets: [{
                            label: 'Daily POS Sales Revenue ($)',
                            data: salesData.map(d => d.total_sales),
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.15)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });

                // Render Expense Pie Chart
                const ctxExp = document.getElementById('expenseReportChart').getContext('2d');
                expenseChart = new Chart(ctxExp, {
                    type: 'doughnut',
                    data: {
                        labels: financeData.expenses_by_category.map(e => e.category),
                        datasets: [{
                            label: 'Operating Expenses ($)',
                            data: financeData.expenses_by_category.map(e => e.total_amount),
                            backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#a855f7']
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            } catch (err) {}
        }

        // 10. SETTINGS LOADERS
        async function fetchSettingsData() {
            try {
                const resBranch = await fetch('/api/settings/branches');
                const branchesData = await resBranch.json();

                document.getElementById('settings-branches-rows').innerHTML = branchesData.map(b => `
                    <tr>
                        <td><strong>${b.name}</strong></td>
                        <td>${b.address || 'N/A'}</td>
                        <td>
                            <button onclick="deleteBranch(${b.id})" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">Remove</button>
                        </td>
                    </tr>
                `).join('');

                const resSettings = await fetch('/api/settings');
                const settingsData = await resSettings.json();
                
                document.getElementById('set-currency-code').value = settingsData.currency_code;
                document.getElementById('set-currency-symbol').value = settingsData.currency_symbol;
                document.getElementById('set-tax-percent').value = settingsData.tax_percentage;
            } catch (err) {}
        }

        function showBranchModal() { openModal('branch-modal'); }

        async function deleteBranch(id) {
            if (confirm("Decommission this branch location?")) {
                const res = await fetch(`/api/settings/branches/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
                const data = await res.json();
                if (res.ok) {
                    showToast(data.message, 'success');
                    fetchSettingsData();
                } else {
                    showToast(data.error || 'Failed to remove branch.', 'danger');
                }
            }
        }

        // 11. SUPER ADMIN SAAS TENANT MANAGER
        async function fetchSaaSShops() {
            try {
                const res = await fetch('/saas/admin/shops');
                const shopsData = await res.json();

                document.getElementById('saas-shops-rows').innerHTML = shopsData.map(s => `
                    <tr>
                        <td><strong>${s.name}</strong></td>
                        <td>${s.owner_name}</td>
                        <td>${s.email}</td>
                        <td><span class="badge badge-warning">${s.subscription ? s.subscription.name : 'Free'}</span></td>
                        <td><span class="badge ${s.status === 'approved' ? 'badge-success' : 'badge-warning'}">${s.status}</span></td>
                        <td>
                            ${s.status === 'pending' ? `
                                <button onclick="approveShop(${s.id})" class="btn btn-primary" style="padding: 4px 8px; font-size: 11px;">Approve</button>
                                <button onclick="rejectShop(${s.id})" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">Reject</button>
                            ` : ''}
                            ${s.status === 'approved' ? `
                                <button onclick="suspendShop(${s.id})" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">Suspend</button>
                            ` : ''}
                            ${s.status === 'suspended' ? `
                                <button onclick="activateShop(${s.id})" class="btn btn-primary" style="padding: 4px 8px; font-size: 11px;">Activate</button>
                            ` : ''}
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="6" style="text-align: center;">No shop registration requests found.</td></tr>';
            } catch (err) {}
        }

        async function approveShop(id) {
            const res = await fetch(`/saas/admin/shops/${id}/approve`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
            const data = await res.json();
            showToast(data.message, 'success');
            fetchSaaSShops();
        }

        async function rejectShop(id) {
            const res = await fetch(`/saas/admin/shops/${id}/reject`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
            const data = await res.json();
            showToast(data.message, 'warning');
            fetchSaaSShops();
        }

        async function suspendShop(id) {
            const res = await fetch(`/saas/admin/shops/${id}/suspend`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
            const data = await res.json();
            showToast(data.message, 'warning');
            fetchSaaSShops();
        }

        async function activateShop(id) {
            const res = await fetch(`/saas/admin/shops/${id}/activate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
            const data = await res.json();
            showToast(data.message, 'success');
            fetchSaaSShops();
        }

        function showRegisterModal() { openModal('register-modal'); }
    </script>
</body>
</html>
