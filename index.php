<?php
// index.php - Main page (tables only)
require_once 'db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
     <title>Restaurant POS System - Tables</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 15px 0;
            text-align: center;
            margin-bottom: 20px;
        }
        .table-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 15px;
            padding: 15px;
        }
        .table-btn {
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            border-radius: 10px;
            transition: all 0.3s;
            text-decoration: none;
            flex-direction: column;
            padding: 10px;
        }
        .table-available {
            background-color: #28a745;
            color: white;
        }
        .table-occupied {
            background-color: #dc3545;
            color: white;
        }
        .badge-status {
            font-size: 0.7em;
            padding: 3px 6px;
            border-radius: 10px;
            margin-top: 5px;
        }
        .btn-group-custom {
            display: flex;
            gap: 10px;
        }
        .table-number {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 3px;
        }
        .table-name {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Updated header text to English -->
        <h1>Restaurant POS System - Tables</h1>
    </div>

    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <!-- Updated page title to English -->
            <h3>Table Selection</h3>
            <div class="btn-group-custom">
                <a href="admin.php" class="btn btn-secondary">
                    <!-- Updated button text to English -->
                    <i class="fas fa-arrow-left me-2"></i>Back to Admin Panel
                </a>
                <a href="masa_yonetimi.php" class="btn btn-warning">
                    <!-- Updated button text to English -->
                    <i class="fas fa-chair me-2"></i>Table Management
                </a>
                <a href="menu_yonetimi.php" class="btn btn-primary">
                    <!-- Updated button text to English -->
                    <i class="fas fa-utensils me-2"></i>Menu Management
                </a>
                <a href="opsiyon_yonetimi.php" class="btn btn-primary">
                    <!-- Updated button text to English -->
                    <i class="fas fa-cog me-2"></i>Option Management
                </a>
                <a href="printer_yonetimi.php" class="btn btn-info">
                    <!-- Updated button text to English -->
                    <i class="fas fa-print me-2"></i>Printer Management
                </a>
            </div>
        </div>
        <div class="table-container" id="tablesGrid">
            <!-- Tables will be added here by JavaScript -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let tables = [];

        // When page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadTables();
            
            // Refresh tables every 30 seconds
            setInterval(loadTables, 30000);
        });

        // Load tables
        function loadTables() {
            fetch('db.php?action=get_tables')
                .then(response => response.json())
                .then(data => {
                    tables = data;
                    renderTables();
                })
                .catch(error => {
                    console.error('Error loading tables:', error);
                    document.getElementById('tablesGrid').innerHTML = `
                        <div class="col-12 text-center text-danger">
                            <p>Error loading tables. Please refresh the page.</p>
                            <button class="btn btn-primary" onclick="loadTables()">
                                <i class="fas fa-refresh me-2"></i>Refresh
                            </button>
                        </div>
                    `;
                });
        }

        // Display tables
        function renderTables() {
            const tablesGrid = document.getElementById('tablesGrid');
            tablesGrid.innerHTML = '';
            
            if (tables.length === 0) {
                tablesGrid.innerHTML = `
                    <div class="col-12 text-center text-muted">
                        <p>No tables found.</p>
                        <a href="masa_yonetimi.php" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Add Table
                        </a>
                    </div>
                `;
                return;
            }
            
            tables.forEach(table => {
                const tableElement = document.createElement('a');
                tableElement.className = `table-btn ${table.status === 'available' ? 'table-available' : 'table-occupied'}`;
                tableElement.href = `siparis.php?table_id=${table.id}`;
                
                // Show both table name and number
                tableElement.innerHTML = `
                    <div class="table-name">${table.name || 'Table'} ${table.number}</div>
                    ${table.status === 'occupied' ? 
                        '<span class="badge bg-light text-dark badge-status"><i class="fas fa-users me-1"></i>Occupied</span>' : 
                        '<span class="badge bg-light text-dark badge-status"><i class="fas fa-check me-1"></i>Available</span>'
                    }
                `;
                
                tablesGrid.appendChild(tableElement);
            });
        }

        // Page refresh button
        function refreshPage() {
            loadTables();
        }
        
        // Update table status
        function updateTableStatus(tableId, status) {
            fetch('db.php?action=update_table_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `table_id=${tableId}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Table status updated successfully');
                } else {
                    console.error('Error updating table status:', data.error);
                }
            })
            .catch(error => console.error('Error updating table status:', error));
        }
    </script>
</body>
</html>
