function saveCurrencies() {
    const form = document.getElementById("currencyForm");
    const formData = new FormData(form);

    fetch("/currency/update-all", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                .value,
            Accept: "application/json",
        },
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                // Alert user to reload page
                alert(
                    data.message +
                        "\nPlease reload the page to see the new currency.",
                );

                // Reset new currency inputs
                form.querySelector('input[name="new_currency[factor]"]').value =
                    "";
                form.querySelector('input[name="new_currency[name]"]').value =
                    "";
                form.querySelector('input[name="new_currency[code]"]').value =
                    "";
            } else {
                console.error(data.message);
                alert("Error: " + data.message);
            }
        })
        .catch((err) => {
            console.error(err);
            alert("Server error. Check console for details.");
        });
}

// Refresh Button
const refreshBtn = document.getElementById("refreshBtn");
const unsaveModal = document.getElementById("unsaveModal");
const cancelBtn = unsaveModal.querySelector("[data-modal-close]");
const continueBtn = unsaveModal.querySelector("[data-modal-action]");

// Flag to simulate unsaved work (you can replace this with your real check)
let hasUnsavedWork = true;

refreshBtn.addEventListener("click", () => {
    if (hasUnsavedWork) {
        // Show modal
        unsaveModal.classList.remove("hidden");
    } else {
        // No unsaved work, refresh directly
        location.reload();
    }
});

// Close modal
cancelBtn.addEventListener("click", () => {
    unsaveModal.classList.add("hidden");
});

// Confirm refresh
continueBtn.addEventListener("click", () => {
    unsaveModal.classList.add("hidden");
    location.reload(); // actually refresh the page
});

// How to use Toast
// // Success
// showToast({ message: 'Customer deleted successfully', type: 'success' });

// // Error
// showToast({ message: 'Failed to delete customer', type: 'error' });

// // Warning
// showToast({ message: 'Please select a customer first', type: 'warning' });

let toastTimeout;

function showToast({ message, type = "success", duration = 3000 }) {
    const toast = document.getElementById("toastMessage");
    const text = document.getElementById("toastText");
    const icon = document.getElementById("toastIcon");

    // Set message
    text.innerText = message;

    // Set icon and color
    switch (type) {
        case "success":
            toast.classList.remove("bg-red-500", "bg-yellow-500");
            icon.innerText = "✔️";
            icon.classList.add("text-green-500");
            break;
        case "error":
            toast.classList.remove("bg-green-500", "bg-yellow-500");
            icon.innerText = "❌";
            icon.classList.add("text-red-500");
            break;
        case "warning":
            toast.classList.remove("bg-green-500", "bg-red-500");
            icon.innerText = "⚠️";
            icon.classList.add("text-yellow-500");
            break;
    }

    toast.classList.remove("hidden");

    // Auto hide after duration
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        hideToast();
    }, duration);
}
function hideToast() {
    const toast = document.getElementById("toastMessage");
    toast.classList.add("hidden");

    // Optional: reset icon and text
    document.getElementById("toastText").innerText = "";
    document.getElementById("toastIcon").innerText = "✔️";
}
async function confirmDeleteCustomer() {
    const customerId = getSelectedCustomerId();
    if (!customerId) return;

    // close modal
    closeDeleteCustModal();

    try {
        const res = await fetch(`/customers/${customerId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                    .value,
                Accept: "application/json",
            },
        });

        if (!res.ok) throw new Error();

        // ✅ remove row safely using data-id
        const row = document.querySelector(`tr[data-id="${customerId}"]`);
        if (row) row.remove();

        // show success toast
        showToast({
            message: "Customer deleted successfully",
            type: "success",
        });
    } catch (err) {
        showToast({ message: "Delete failed", type: "error" });
        console.error(err);
    }
}

async function confirmUpdateCustomer() {
    if (!selectedCustomerId) {
        showToast({ message: "No customer selected!", type: "warning" });
        return;
    }
    // 🔹 Validate first
    if (!validateUpdateCustomerForm()) return; // stop if invalid

    const id = selectedCustomerId;

    const payload = {
        customer_code: document.getElementById("cust-customer_code").value,
        name: document.getElementById("cust-name").value,
        phone: document.getElementById("cust-phone").value,
        email: document.getElementById("cust-email").value,
        address: document.getElementById("cust-address").value,
        city: document.getElementById("cust-city").value,
        country: document.getElementById("cust-country").value,
        type: document.getElementById("cust-type").value,
        credit_limit:
            parseFloat(document.getElementById("cust-credit").value) || 0,
        balance: parseFloat(document.getElementById("cust-balance").value) || 0,
        point: parseInt(document.getElementById("cust-point").value) || 0,
        status: parseInt(document.getElementById("cust-status").value),
    };
    console.log("Updating customer with payload:", payload);
    try {
        const res = await fetch(`/customers/${id}`, {
            method: "PUT",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                    .value,
                Accept: "application/json",
                "Content-Type": "application/json", // 🔥 must have
            },
            body: JSON.stringify(payload),
        });

        if (!res.ok) throw new Error("Update failed");

        const updatedCustomer = await res.json();

        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (!row) return console.warn("Row not found!");

        row.querySelector("td:nth-child(3)").textContent =
            updatedCustomer.name ?? "-";
        row.querySelector("td:nth-child(4)").textContent =
            updatedCustomer.phone ?? "-";
        row.querySelector("td:nth-child(5)").textContent =
            updatedCustomer.email ?? "-";

        // optional dataset for internal use
        row.dataset.address = updatedCustomer.address ?? "";
        row.dataset.city = updatedCustomer.city ?? "";
        row.dataset.country = updatedCustomer.country ?? "";
        row.dataset.credit = updatedCustomer.credit_limit ?? 0;
        row.dataset.balance = updatedCustomer.balance ?? 0;
        row.dataset.point = updatedCustomer.point ?? 0;
        row.dataset.status = updatedCustomer.status ?? 0;

        // status badge
        const statusTd = row.querySelector("td:nth-child(11)");
        if (statusTd) {
            statusTd.innerHTML =
                updatedCustomer.status == 1
                    ? `<span class="inline-flex items-center bg-success-soft border border-success-subtle text-fg-success-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">
                <span class="w-2 h-2 me-1 bg-success rounded-full"></span>
                &ensp;Active
           </span>`
                    : `<span class="inline-flex items-center bg-danger-soft border border-danger-subtle text-fg-danger-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">
                <span class="w-2 h-2 me-1 bg-danger rounded-full"></span>
                &ensp;Inactive
           </span>`;
        }

        showToast({
            message: "Customer updated successfully",
            type: "success",
        });
        closeUpdateCustModal();
    } catch (err) {
        console.error(err);
        showToast({ message: "Failed to update customer", type: "error" });
    }
}

// Data Customer Search & Pagination
const searchInput = document.getElementById("customerSearchInput");
const typeSelect = document.getElementById("customerTypeSelect");
const activeCheckbox = document.getElementById("customerSearchCheckbox");
const tbody = document.getElementById("customer-table-body");

let customers = []; // store async fetched data
let sortColumn = ""; // e.g., 'name', 'credit_limit'
let sortDirection = "asc"; // 'asc' or 'desc'
async function loadCustomers(page = 1) {
    const search = searchInput.value;
    const type = typeSelect.value;
    const active = activeCheckbox.checked ? 1 : 0;

    const query = new URLSearchParams({
        page,
        limit: 20,
        search,
        type,
        status: active,
        sort_by: sortColumn, // NEW
        sort_dir: sortDirection, // NEW
    });
    const res = await fetch(`/customers/list_search?${query.toString()}`);
    const result = await res.json();

    renderCustomerTable(result.data);

    const pagination = document.getElementById("paginationContainer");
    pagination.innerHTML = ""; // clear previous buttons

    const current = result.current_page;
    const last = result.last_page;

    // Always show "First" if not on page 1
    if (current > 1) {
        const firstBtn = document.createElement("button");
        firstBtn.type = "button"; // <-- prevents form submit
        firstBtn.textContent = "« First";
        firstBtn.className = "px-3 py-1 bg-gray-200 rounded";
        firstBtn.onclick = () => loadCustomers(1);
        pagination.appendChild(firstBtn);
    }

    // ----------------- NEW PAGE LOGIC -----------------
    const maxVisible = 10; // show 5 numeric buttons
    let start = Math.max(1, current - 2);
    let end = Math.min(last, current + 2);

    // Adjust if near start
    if (current <= 2) {
        end = Math.min(last, maxVisible);
    }

    // Adjust if near end
    if (current >= last - 1) {
        start = Math.max(1, last - (maxVisible - 1));
    }
    // --------------------------------------------------

    // Numeric buttons
    for (let i = start; i <= end; i++) {
        const pageBtn = document.createElement("button");
        pageBtn.type = "button"; // <-- prevents form submit
        pageBtn.textContent = i;
        pageBtn.className =
            "px-3 py-1 rounded " +
            (i === current ? "bg-emerald-500 text-white" : "bg-gray-200");
        pageBtn.onclick = () => loadCustomers(i);
        pagination.appendChild(pageBtn);
    }

    // Always show "Last" if not on last page
    if (current < last) {
        const lastBtn = document.createElement("button");
        lastBtn.type = "button"; // <-- prevents form submit
        lastBtn.textContent = "Last »";
        lastBtn.className = "px-3 py-1 bg-gray-200 rounded";
        lastBtn.onclick = () => loadCustomers(last);
        pagination.appendChild(lastBtn);
    }

    // Update page info text
    document.getElementById("pageInfo").textContent =
        `Page ${current} of ${last} | Total ${result.total}`;
}

// Render table rows
function renderCustomerTable(data) {
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                    No customers found
                </td>
            </tr>
        `;
        return;
    }
    let count = 0;
    tbody.innerHTML = data
        .map((c) => {
            count++; // increment here for each customer
            return `
        <tr class="border-t hover:bg-neutral-tertiary cursor-pointer"
            data-id="${c.id}"
            data-customer_code="${c.customer_code ?? ""}"
            data-address="${c.address ?? ""}"
            data-city="${c.city ?? ""}"
            data-country="${c.country ?? ""}"
            data-credit="${c.credit_limit}"
            data-balance="${c.balance}"
            data-point="${c.point}"
            data-status="${c.status}">
            <td><input type="radio" name="customer_id" value="${c.id}"></td>
            <td>${c.id}</td>
            <td>${c.customer_code ?? "-"}</td>
            <td>${c.name}</td>
            <td>${c.phone ?? "-"}</td>
            <td>${c.email ?? "-"}</td>
            <td>${c.type}</td>
            <td>${parseFloat(c.credit_limit).toFixed(2)}</td>
            <td>${parseFloat(c.balance).toFixed(2)}</td>
            <td>${c.point}</td>
            <td>
                ${
                    Number(c.status) === 1
                        ? `<span class="inline-flex items-center bg-success-soft border border-success-subtle text-fg-success-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">
                             <span class="w-2 h-2 me-1 bg-success rounded-full"></span>
                             &ensp;Active
                           </span>`
                        : `<span class="inline-flex items-center bg-danger-soft border border-danger-subtle text-fg-danger-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">
                             <span class="w-2 h-2 me-1 bg-danger rounded-full"></span>
                             &ensp;Inactive
                           </span>`
                }
            </td>
        </tr>
    `;
        })
        .join("");
}
document.querySelectorAll("th[data-column]").forEach((th) => {
    th.addEventListener("click", () => {
        const col = th.dataset.column;

        if (sortColumn === col) {
            // toggle direction
            sortDirection = sortDirection === "asc" ? "desc" : "asc";
        } else {
            sortColumn = col;
            sortDirection = "asc";
        }

        loadCustomers(1); // reload page 1 with new sort

        // Update icons
        document
            .querySelectorAll(".sort-icon")
            .forEach((s) => (s.textContent = "↕"));
        th.querySelector(".sort-icon").textContent =
            sortDirection === "asc" ? "↑" : "↓";
    });
});

window.addEventListener("DOMContentLoaded", () => {
    const openModalBtn = document.getElementById("openCustomerModal");

    openModalBtn.addEventListener("click", () => loadCustomers(1));

    searchInput.addEventListener("input", () => loadCustomers(1));
    typeSelect.addEventListener("change", () => loadCustomers(1));
    activeCheckbox.addEventListener("change", () => loadCustomers(1));
});

// Data Product Search & Pagination
const searchInput_product_list = document.getElementById("ProductSearchInput");
const typeSelect_product = document.getElementById("productTypeSelect");
const activeCheckbox_product = document.getElementById("productSearchCheckbox");
const productLimitSelect = document.getElementById("productLimitSelect");
const tbody_product = document.getElementById("product-table-body");

let products = []; // store async fetched data
let sortColumn_product = ""; // e.g., 'name', 'credit_limit'
let sortDirection_product = "asc"; // 'asc' or 'desc'

window.addEventListener("DOMContentLoaded", () => {
    const openProductModalBtn = document.getElementById("openProductModal");

    openProductModalBtn.addEventListener("click", () => loadProducts(1));
    searchInput_product_list.addEventListener("input", () => loadProducts(1));
    typeSelect_product.addEventListener("change", () => loadProducts(1));

    activeCheckbox_product.addEventListener("change", () => loadProducts(1));
    productLimitSelect.addEventListener("change", () => loadProducts(1));
});

async function loadProducts(page = 1) {
    const search = searchInput_product_list.value;
    const type = typeSelect_product.value;
    const active = activeCheckbox_product.value || "";

    let limit = parseInt(productLimitSelect.value) || 15;
    const query = new URLSearchParams({
        page,
        limit: limit,
        search,
        type,
        status: active,
        sort_by: sortColumn_product, // NEW
        sort_dir: sortDirection_product, // NEW
    });
    const res = await fetch(`/products/list_search?${query.toString()}`);
    const result = await res.json();

    renderProductTable(result.data);

    const pagination = document.getElementById("paginationContainerProduct");
    pagination.innerHTML = ""; // clear previous buttons

    const current = result.current_page;
    const last = result.last_page;

    // Always show "First" if not on page 1
    if (current > 1) {
        const firstBtn = document.createElement("button");
        firstBtn.type = "button"; // <-- prevents form submit
        firstBtn.textContent = "« First";
        firstBtn.className = "px-3 py-1 bg-gray-200 rounded";
        firstBtn.onclick = () => loadProducts(1);
        pagination.appendChild(firstBtn);
    }

    // ----------------- NEW PAGE LOGIC -----------------
    const maxVisible = 10; // show 5 numeric buttons
    let start = Math.max(1, current - 2);
    let end = Math.min(last, current + 2);

    // Adjust if near start
    if (current <= 2) {
        end = Math.min(last, maxVisible);
    }

    // Adjust if near end
    if (current >= last - 1) {
        start = Math.max(1, last - (maxVisible - 1));
    }
    // --------------------------------------------------

    // Numeric buttons
    for (let i = start; i <= end; i++) {
        const pageBtn = document.createElement("button");
        pageBtn.type = "button"; // <-- prevents form submit
        pageBtn.textContent = i;
        pageBtn.className =
            "px-3 py-1 rounded " +
            (i === current ? "bg-emerald-500 text-white" : "bg-gray-200");
        pageBtn.onclick = () => loadProducts(i);
        pagination.appendChild(pageBtn);
    }

    // Always show "Last" if not on last page
    if (current < last) {
        const lastBtn = document.createElement("button");
        lastBtn.type = "button"; // <-- prevents form submit
        lastBtn.textContent = "Last »";
        lastBtn.className = "px-3 py-1 bg-gray-200 rounded";
        lastBtn.onclick = () => loadProducts(last);
        pagination.appendChild(lastBtn);
    }

    // Update page info text
    document.getElementById("pageInfo").textContent =
        `Page ${current} of ${last} | Total ${result.total}`;
}

// Render product table rows
function renderProductTable(data) {
    if (data.length === 0) {
        tbody_product.innerHTML = `
            <tr>
                <td colspan="20" class="px-4 py-4 text-center text-gray-500">
                    No Products found
                </td>
            </tr>
        `;
        return;
    }

    let count = 0;
    tbody_product.innerHTML = data
        .map((p) => {
            count++; // increment for each product
            return `
        <tr class="border-t hover:bg-neutral-tertiary cursor-pointer text-nowrap"
            data-id="${p.id}"
                    data-code="${p.bar_code ?? ""}"
            data-code="${p.code ?? ""}"
            data-name="${p.name ?? ""}"
            data-variant="${p.variant ?? ""}"
            data-description="${p.description ?? ""}"
            data-min_stock="${p.min_stock}"
            data-max_stock="${p.max_stock}"
            data-sell_price="${p.sell_price}"
            data-cost="${p.cost}"
            data-vat="${p.vat}"
            data-discount_percent="${p.discount_percent}"
            data-last_purchase_price="${p.last_purchase_price ?? ""}"
            data-category_name="${p.category?.name ?? ""}"
            data-unit="${p.unit ?? ""}"
            data-track_stock="${p.track_stock}"
            data-allow_discount="${p.allow_discount}"
            data-allow_return="${p.allow_return}"
            data-status="${p.status}">

            <td><input type="radio" name="product_id" value="${p.id}"></td>
            <td>${p.id}</td>
            <td>${p.bar_code ?? "-"}</td>
                        <td>${p.code ?? "-"}</td>
            <td>${p.name}</td>
            <td>${p.variant ?? "-"}</td>
            <td>${p.description ?? "-"}</td>
            <td>${p.min_stock}</td>
            <td>${p.max_stock}</td>
            <td>${parseFloat(p.sell_price).toFixed(2)}</td>
            <td>${parseFloat(p.cost).toFixed(2)}</td>
            <td>${parseFloat(p.vat).toFixed(2)}</td>
            <td>${parseFloat(p.discount_percent).toFixed(2)}</td>
            <td>${p.last_purchase_price ? parseFloat(p.last_purchase_price).toFixed(2) : "-"}</td>
            <td>${p.category?.name ?? "-"}</td>
            <td>${p.unit ?? "-"}</td>
            <td>${p.track_stock ? "Yes" : "No"}</td>
            <td>${p.allow_discount ? "Yes" : "No"}</td>
            <td>${p.allow_return ? "Yes" : "No"}</td>
            <td>
                ${
                    Number(p.status) === 1
                        ? `<span class="inline-flex items-center bg-success-soft border border-success-subtle text-fg-success-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">
                             <span class="w-2 h-2 me-1 bg-success rounded-full"></span>
                             &ensp;Active
                           </span>`
                        : `<span class="inline-flex items-center bg-danger-soft border border-danger-subtle text-fg-danger-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">
                             <span class="w-2 h-2 me-1 bg-danger rounded-full"></span>
                             &ensp;Inactive
                           </span>`
                }
            </td>
        </tr>
        `;
        })
        .join("");
}

// Load categories on first click
typeSelect_product.addEventListener("click", async () => {
    if (typeSelect_product.options.length > 0) return; // already loaded
    await CategoryLoad();
});
// Example CategoryLoad function
async function CategoryLoad() {
    try {
        const response = await fetch("/categories"); // your API endpoint
        const categories = await response.json();

        // Clear existing options (optional)
        typeSelect_product.innerHTML =
            '<option value="">Select Category</option>';
        console.log("Fetched categories:", categories);
        categories.forEach((cat) => {
            const option = document.createElement("option");
            option.value = cat.id; // adjust to your API field
            option.textContent = cat.name;
            typeSelect_product.appendChild(option);
        });
    } catch (error) {
        console.error("Failed to load categories:", error);
    }
}

function validateUpdateCustomerForm() {
    const errors = [];

    const name = document.getElementById("cust-name").value.trim();
    const type = document.getElementById("cust-type").value;
    const status = document.getElementById("cust-status").value;
    const email = document.getElementById("cust-email").value.trim();

    // 1️⃣ Required fields
    if (!name) errors.push("Name is required.");

    if (!["walk_in", "member", "vip"].includes(type)) {
        errors.push("Type must be Walk-in, Member, or VIP.");
    }

    if (status !== "0" && status !== "1") {
        errors.push("Status must be Active or Inactive.");
    }

    // 2️⃣ Optional but check if email is valid
    if (email && !/^\S+@\S+\.\S+$/.test(email)) {
        errors.push("Email is invalid.");
    }

    // 3️⃣ Return result
    if (errors.length > 0) {
        errors.forEach((err) => showToast({ message: err, type: "error" }));
        return false; // invalid
    }

    return true; // valid
}

document
    .getElementById("openWarehouseModel")
    .addEventListener("click", async function () {
        await loadWarehouses();
    });
async function loadWarehouses() {
    const tbody = document.getElementById("warehouse-table-body");
    tbody.innerHTML = `
        <tr>
            <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                Loading...
            </td>
        </tr>
    `;

    try {
        const response = await fetch("/warehouses/list");
        if (!response.ok) throw new Error("Fetch failed");

        const warehouses = await response.json();
        tbody.innerHTML = "";

        if (warehouses.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                        No warehouses found
                    </td>
                </tr>
            `;
            return;
        }

        warehouses.forEach((w) => {
            tbody.insertAdjacentHTML(
                "beforeend",
                `
                <tr class="border-b hover:bg-neutral-tertiary cursor-pointer">
                    <td class="px-4 py-2">
                        <input type="radio" name="warehouse_id"
                               value="${w.id}"
                               data-name="${w.name}"
                               data-location="${w.location ?? ""}">
                    </td>
                    <td class="px-4 py-2">${w.id}</td>
                    <td class="px-4 py-2 font-medium">${w.name}</td>
                    <td class="px-4 py-2">${w.location ?? "-"}</td>
                    <td class="px-4 py-2 text-center">${w.total_stock ?? 0}</td>
                </tr>
            `,
            );
        });
    } catch (err) {
        console.error(err);
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-4 text-center text-red-500">
                    Failed to load warehouses
                </td>
            </tr>
        `;
    }
}
document.addEventListener("change", function (e) {
    if (e.target.name === "warehouse_id") {
        const name = e.target.dataset.name;
        const location = e.target.dataset.location;

        console.log("Selected warehouse:", name, location, e);
    }
});
function getSelectedWarehouse() {
    return document.querySelector('input[name="warehouse_id"]:checked');
}

// ------------------------
// Confirmation Modal Logic
// ------------------------
let warehouseConfirmCallback = null;

function openWarehouseConfirm({
    title,
    message,
    onConfirm,
    danger = false,
    confirmText = "Confirm",
}) {
    document.getElementById("warehouseConfirmTitle").innerText = title;
    document.getElementById("warehouseConfirmMessage").innerText = message;

    const btn = document.getElementById("warehouseConfirmAction");
    btn.innerText = confirmText;

    btn.className = danger
        ? "px-5 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition"
        : "px-5 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition";

    warehouseConfirmCallback = onConfirm;
    document.getElementById("warehouseConfirmModal").classList.remove("hidden");
}

function closeWarehouseConfirm() {
    document.getElementById("warehouseConfirmModal").classList.add("hidden");
    warehouseConfirmCallback = null;
}

document
    .getElementById("warehouseConfirmAction")
    ?.addEventListener("click", async () => {
        if (warehouseConfirmCallback) await warehouseConfirmCallback();
        closeWarehouseConfirm();
    });

document.querySelectorAll("[data-warehouse-close]").forEach((btn) => {
    btn.addEventListener("click", closeWarehouseConfirm);
});

// ------------------------
// Edit Warehouse Button
// ------------------------
document.getElementById("btnEditWarehouse")?.addEventListener("click", () => {
    const selected = document.querySelector(
        'input[name="warehouse_id"]:checked',
    );
    if (!selected) {
        return showToast({
            message: "Please select a warehouse",
            type: "error",
        });
    }

    document.getElementById("edit_warehouse_name").value =
        selected.dataset.name;
    document.getElementById("edit_warehouse_location").value =
        selected.dataset.location;

    openWarehouseConfirm({
        title: "Update Warehouse",
        message: `Edit name & location for "${selected.dataset.name}"`,
        danger: false,
        confirmText: "Update",
        onConfirm: async () => {
            await updateWarehouse(selected.value);
        },
    });
});

// ------------------------
// Update Warehouse AJAX
// ------------------------
async function updateWarehouse(id) {
    const name = document.getElementById("edit_warehouse_name").value;
    const location = document.getElementById("edit_warehouse_location").value;

    try {
        const response = await fetch(`/warehouses/update/${id}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                    .value,
                Accept: "application/json",
                "Content-Type": "application/json", // 🔥 must have
            },
            body: JSON.stringify({ name, location }),
        });

        const result = await response.json();

        if (result.success) {
            loadWarehouses();
            // show success toast
            showToast({
                message: "Warehouse updated successfully!",
                type: "success",
            });
        } else {
            showToast({
                message: result.message,
                type: "error",
            });
        }
    } catch (err) {
        console.error(err);
        alert("Error updating warehouse");
    }
}

const modal = document.getElementById("warehouse-stock-modal");
const tbody_stock = document.getElementById("warehouse-stock-tbody");
const closeBtn = document.getElementById("close-modal");
const searchInput_stock = document.getElementById("search-stock");
const statusFilter = document.getElementById("status-filter");
document
    .getElementById("view-stock-warehouse")
    .addEventListener("click", async () => {
        const selected = getSelectedWarehouse();
        if (!selected) {
            showToast({
                message: "Please select a warehouse first",
                type: "error",
            });
            return;
        }
        modal.classList.remove("hidden");

        await loadWarehouseStock(selected.value);
    });
// Close modal
closeBtn.addEventListener("click", () => modal.classList.add("hidden"));

let currentSort = {
    by: "expire", // default sort column
    dir: "asc", // default direction
};
let currentWarehouseId = null;

async function loadWarehouseStock(warehouseId) {
    try {
        const tbody_stock = document.getElementById("warehouse-stock-tbody");
        tbody_stock.innerHTML = `
            <tr>
                <td colspan="14" class="px-4 py-4 text-center text-rose-500">
                    Loading...
                </td>
            </tr>
        `;

        currentWarehouseId = warehouseId;
        // Only keep active filters
        const params = new URLSearchParams({
            search: document.getElementById("search-stock").value,
            variant: document.getElementById("variant-filter").value,
            status: document.getElementById("status-filter").value,
            stock: document.getElementById("stock-filter").value,
        });

        const res = await fetch(`/warehouses/${warehouseId}/stock?${params}`);
        const data = await res.json();
        console.log("Warehouse stock data:", data);
        renderStockTable(data.products ?? [], data.warehouse.name);
    } catch (err) {
        console.error(err);
        alert("Error fetching stock");
    }
}
// Listen to filter inputs (search, variant, status, stock)
document
    .querySelectorAll(
        "#search-stock,#variant-filter,#status-filter,#stock-filter",
    )
    .forEach((el) => {
        el.addEventListener("input", () =>
            loadWarehouseStock(currentWarehouseId),
        );
    });

function renderStockTable(products, warehouseName) {
    tbody_stock.innerHTML = "";
    const wh_name = document.getElementById("wh_name");
    wh_name.textContent = `Warehouse : ${warehouseName}`;

    products.forEach((p, index) => {
        // Format expire date nicely: YYYY-MM-DD → DD/MM/YYYY
        let expireText = "N/A";
        if (p.expire) {
            const d = new Date(p.expire);
            const day = String(d.getDate()).padStart(2, "0");
            const month = String(d.getMonth() + 1).padStart(2, "0");
            const year = d.getFullYear();
            expireText = `${day}/${month}/${year}`;
        }

        tbody_stock.insertAdjacentHTML(
            "beforeend",
            `
            <tr class="hover:bg-green-50 cursor-pointer transition-colors">
                <td class="px-3  text-left text-sm text-gray-600">${index + 1}</td>
                <td class="px-3  text-left text-sm text-gray-600 font-medium truncate max-w-xs" title="${p.code ?? ""}">${p.code ?? ""}</td>
                <td class="px-3  text-left text-sm text-gray-800 font-medium truncate max-w-xs" title="${p.product_name}">${p.product_name}</td>
                <td class="px-3  text-left text-sm text-gray-600 truncate max-w-xs" title="${p.variant ?? ""}">${p.variant ?? ""}</td>
                <td class="px-3  text-left text-sm text-gray-600 truncate max-w-xs" title="${p.description ?? ""}">${p.description ?? ""}</td>
                <td class="px-3  text-left text-sm text-gray-600 truncate max-w-xs" title="${p.lot}">${p.lot ?? "NOLOT"}</td>
                <td class="px-3  text-left text-sm text-gray-600 truncate max-w-xs" title="${expireText}">${expireText}</td>
                <td class="px-3  text-center text-sm font-bold" title="Stock">${p.qty}</td>
                <td class="px-3  text-center text-sm text-gray-600 font-semibold">${p.unit}</td>
                <td class="px-3  text-right text-sm text-gray-600">${p.cost_price ?? 0}</td>
                <td class="px-3  text-right text-sm text-gray-600">${p.vat ?? 0}</td>
                <td class="px-3  text-right text-sm text-gray-600">${p.sell_price ?? 0}</td>
                <td class="px-3  text-right text-sm text-gray-600">${p.sell_price_vat ?? 0}</td>
                <td class="px-3  text-left text-sm text-gray-600 truncate max-w-xs" title="${p.category_name ?? ""}">${p.category_name ?? ""}</td>
                <td class="px-3  text-center text-sm font-semibold ${p.status ? "text-green-600" : "text-red-500"}">${p.status ? "Active" : "Inactive"}</td>
            </tr>
        `,
        );
    });
}

const warehouseModal = document.getElementById("warehouse-stock-modal");
const modalBox = warehouseModal.querySelector("div.bg-white");

function openWarehouseModal() {
    warehouseModal.classList.remove("hidden");
    setTimeout(() => {
        warehouseModal.classList.remove("opacity-0");
        modalBox.classList.remove(
            "scale-95",
            "-translate-x-10",
            "-translate-y-10",
            "opacity-0",
        );
        modalBox.classList.add(
            "scale-100",
            "translate-x-0",
            "translate-y-0",
            "opacity-100",
        );
    }, 10);
}

function closeWarehouseModal() {
    // Animate fly out to top-left
    modalBox.classList.remove(
        "scale-100",
        "translate-x-0",
        "translate-y-0",
        "opacity-100",
    );
    modalBox.classList.add(
        "scale-95",
        "-translate-x-20",
        "-translate-y-20",
        "opacity-0",
    );
    warehouseModal.classList.add("opacity-0");

    setTimeout(() => {
        warehouseModal.classList.add("hidden");
        warehouseModal.classList.remove("opacity-0");
        // Reset modal for next open
        modalBox.classList.remove(
            "scale-95",
            "-translate-x-20",
            "-translate-y-20",
            "opacity-0",
        );
        modalBox.classList.add(
            "scale-100",
            "translate-x-0",
            "translate-y-0",
            "opacity-100",
        );
    }, 300); // match transition duration
}

// Close buttons
document
    .getElementById("close-modal")
    .addEventListener("click", closeWarehouseModal);
document
    .getElementById("close-footer-modal")
    .addEventListener("click", closeWarehouseModal);

// Click outside to close
warehouseModal.addEventListener("click", (e) => {
    if (e.target === warehouseModal) closeWarehouseModal();
});

function print(document_type) {
    // check cart Logic
    let input_count_cart = document.getElementById("count_cart_input");
    let count_cart = input_count_cart.value;
    if (count_cart == 0) {
        showToast({
            message: "Cart is Empty.",
            type: "error",
        });
        return;
    }

    // Handle documents that need modals first
    if (document_type === "Receipt") {
        openDatePromt_Modal(() => print_document("Receipt"));
        return;
    } else if (document_type === "Invoice") {
        openDatePromt_Modal(() => print_document("Invoice"));

        return;
    } else if (document_type === "Delivery Note") {
        openDatePromt_Modal(() => print_document("Delivery Note"));
        return;
    }

    // Other documents
    print_document(document_type);
}

function print_document(document_type) {
    if (document_type === "Delivery Note") {
        let deliveryRemark =
            prompt("Enter remark for Delivery Note (optional):") || "";
    }
    // docutment Header
    const document_header = document.getElementById("document-header");

    // Title
    let document_title = document.getElementById("document_title");
    document_title.querySelector("h1").textContent = document_type;

    let logo = document.getElementById("logo");

    const invoiceContent = document.getElementById("invoice").innerHTML;

    // Table
    const table_data = document.getElementById("invoice-table");

    // Shop Info
    const shop_info = document.getElementById("shop_info");

    // customer_info

    const customer_info = document.getElementById("customer_info");

    // table Footer
    const table_footer = document.getElementById("table_footer");
    let table_footer_description = document.getElementById(
        "table_footer_description",
    );

    // Open new window
    const printWindow = window.open("", "_blank", "width=800,height=600");

    printWindow.document.open();

    // Promt User Input

    if (document_type === "Invoice") {
        table_footer_description.innerHTML = `


                `;
        table_footer_description.innerHTML = `
                    <div style="line-height:1.5;">

                            PLEASE MAKE PAYABLE CHEQUE TO MR. RITH SOPHANHA


                    </div>
                `;
        // Format using toLocaleDateString
        const options = { day: "2-digit", month: "short", year: "numeric" };
        const formattedDueDate = due_date.toLocaleDateString("en-GB", options);
           const formattedDocumentDate = document_date.toLocaleDateString("en-GB", options);
        printWindow.document.write(`
                <html>
                <head>
                    <title>Invoice</title>
                    <style>
                        body {  font-family: 'Noto Serif Khmer', serif; font-size: 14px; margin: 20px; color: black; }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
                        th { background-color: #f0f0f0; }
                        .invoice-header h2 { margin: 0; }
                        .font-mid{
                            font-size:12px;
                        }
                        table td ,table th{
                            font-size: 10px;
                        }
                        #seller_name{
                        display:none;
                        }
                        @media print {
                            button { display: none; }
                        }
                    </style>
                </head>
                <body onload="window.print(); window.close();">

                    <!-- Header -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        ${logo.innerHTML}
                        <div style="font-size:25px; font-weight:bold;">
                            ${document_title.innerHTML}
                        </div>
                    </div>

                 <!-- Seller + Date in 2-column grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; ">

                            <!-- Left column: Shop info -->
                            <div  class="font-mid" style="display: grid; gap:3px; text-align: left;">
                                ${shop_info.innerHTML}
                            </div>

                          <!-- Right column: Dates / Invoice (2-grid, all right aligned) -->
                            <div class="font-mid" style="
                                display: grid;
                                grid-template-columns: max-content max-content;

                                justify-content: end;
                                text-align: right;
                            ">
                                <div><b>Date:</b></div>
                                <div>${formattedDocumentDate}</div>

                                <div><b>Invoice #</b></div>
                                <div>


                                </div>

                                <div><b>Due Date:</b></div>
                                <div>${formattedDueDate}</div>
                            </div>


                        </div>





                    <!-- Table -->
                    ${table_data.innerHTML}
                    <div class="font-mid">${table_footer.innerHTML} </div>
                </body>
                </html>
                `);
    } else if (document_type === "Receipt") {
            const options = { day: "2-digit", month: "short", year: "numeric" };
        const formattedDueDate = due_date.toLocaleDateString("en-GB", options);
           const formattedDocumentDate = document_date.toLocaleDateString("en-GB", options);
        table_footer_description.innerHTML = `
                    <div class="font-mid" style="line-height:1.5;">
                        <div style="font-weight:bold; text-decoration:underline; margin-bottom:6px;">
                            <center>Thanks for your Please come again.</center>
                        </div>


                    </div>
                `;

        printWindow.document.write(`
                <html>
                <head>
                    <title>Invoice</title>
                    <style>
                        body {  font-family: 'Noto Serif Khmer', serif; margin: 20px; color: black; }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
                        th { background-color: #f0f0f0; }
                        .invoice-header h2 { margin: 0; }
                        .font-mid{
                            font-size:12px;
                        }
                        #seller_name{
                        display:none;
                        }
                        @media print {
                            button { display: none; }
                        }
                    </style>
                </head>
                <body onload="window.print(); window.close();">

                    <!-- Header -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        ${logo.innerHTML}
                        <div style="font-size:25px; font-weight:bold;">
                            ${document_title.innerHTML}
                        </div>
                    </div>

                 <!-- Seller + Date in 2-column grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">

                            <!-- Left column: Shop info -->
                            <div  class="font-mid"  style="display: grid; gap:3px; text-align: left;">
                                ${shop_info.innerHTML}
                                <strong>Qoutation for:</strong>
                                 ${customer_info.innerHTML}
                            </div>

                          <!-- Right column: Dates / Invoice (2-grid, all right aligned) -->
                            <div class="font-mid" style="
                                display: grid;
                                grid-template-columns: max-content max-content;

                                justify-content: end;
                                text-align: right;
                            ">
                                <div><b>Date:</b></div>
                                <div>${formattedDocumentDate}</div>

                                <div><b>Invoice:</b></div>
                                <div>


                                </div>

                                <div><b>Due Date:</b></div>
                                <div>${formattedDueDate}</div>
                            </div>
                        </div>
                    <!-- Table -->
                    ${table_data.innerHTML}
                    <div class="font-mid">${table_footer.innerHTML} </div>


                </body>
                </html>
                `);
    } else if (document_type === "Delivery Note") {
            const options = { day: "2-digit", month: "short", year: "numeric" };
        const formattedDueDate = due_date.toLocaleDateString("en-GB", options);
           const formattedDocumentDate = document_date.toLocaleDateString("en-GB", options);
        table_footer_description.innerHTML = `
                    <div style=" line-height:1.5;">



                    </div>
                `;

        printWindow.document.write(`
                <html>
                <head>
                    <title>Invoice</title>
                    <style>
                        body {   font-family: 'Noto Serif Khmer', serif; margin: 20px; color: black; }

                        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
                        th { background-color: #f0f0f0; }
                        .invoice-header h2 { margin: 0; }

                        #seller_name{
                        display:none;
                        }
                        #invoice-table th:nth-child(4) ,th:nth-child(5) ,th:nth-child(6) ,  th:nth-child(7){
                        display:none;
                        }
                        #invoice-table th:nth-child(4) ,td:nth-child(5) ,td:nth-child(6) ,  td:nth-child(7){
                        display:none;

                        }
                       .font-mid{
                            font-size:12px;
                        }
                        .total_print{
                        display:none;
                        }
                          #currency_exchange{
                            display:none;}
                        @media print {
                            button { display: none; }
                        }
                    </style>
                </head>
                <body onload="window.print(); window.close();">

                    <!-- Header -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        ${logo.innerHTML}

                    </div>

                 <!-- Seller + Date in 2-column grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">

                            <!-- Left column: Shop info -->
                            <div class="font-mid" style="display: grid; gap:3px; text-align: left;">
                                ${shop_info.innerHTML}
                                <div style="font-size:25px; margin: 5px 5px;  font-weight:bold;">
                                    ${document_title.innerHTML}
                                </div>
                            <br>
                            <strong>Bill To:</strong>
                            ${customer_info.innerHTML}

                            </div>

                          <!-- Right column: Dates / Invoice (2-grid, all right aligned) -->
                            <div class="font-mid" style="
                                display: grid;
                                grid-template-columns: max-content max-content;

                                justify-content: end;
                                text-align: right;
                            ">
                                <div><b>Date:</b></div>
                                <div>${formattedDocumentDate}</div>

                                <div><b>Invoice:</b></div>
                                <div>

                                </div>

                                <div><b>Due Date:</b></div>
                                <div>${formattedDueDate}</div>
                            </div>


                        </div>

                    <!-- Table -->
                    ${table_data.innerHTML}
                    <div class="font-mid">${table_footer.innerHTML} </div>
                </body>
                </html>
                `);
    }
    printWindow.document.close();
}

let document_date = null;
let due_date = null;
let quotationNextAction = null;
let formattedDocDate = null;
let formattedDueDate = null;

function openDatePromt_Modal(onConfirm) {
    const modal = document.getElementById("DatePromptModal");
    const dateInput = document.getElementById("document_dateInput");
    const validInput = document.getElementById("due_date");

    const today = new Date();
    const validUntil = new Date();
    validUntil.setMonth(today.getMonth() + 1);

    const format = (d) => d.toISOString().split("T")[0];

    dateInput.value = format(today);
    validInput.value = format(validUntil);

    modal.classList.remove("hidden");

    // Assign the global callback
    quotationNextAction = onConfirm;

    modal.querySelector("[data-quotation-cancel]").onclick = () => {
        modal.classList.add("hidden");
        quotationNextAction = null; // clear
    };

    modal.querySelector("[data-quotation-confirm]").onclick = () => {
        modal.classList.add("hidden");

        // Parse input values
        document_date = new Date(dateInput.value);
        due_date = new Date(validInput.value);

        // Function to format date as "12 Jan 2026"
        const formatDate = (d) => {
            const options = { day: "2-digit", month: "short", year: "numeric" };
            return d.toLocaleDateString("en-GB", options);
        };

        // Call the callback safely
        if (quotationNextAction && typeof quotationNextAction === "function") {
            quotationNextAction(formattedDocDate, formattedDueDate);
        }

        quotationNextAction = null; // clear after use
    };
}

function closeDatePromtModal() {
    document.getElementById("DatePromptModal").classList.add("hidden");
    quotationNextAction = null; // reset after closing
}

document
    .querySelector("[data-quotation-cancel]")
    .addEventListener("click", () => {
        closeDatePromtModal();
    });

// document
//     .querySelector("[data-quotation-confirm]")
//     .addEventListener("click", () => {
//         const input = document.getElementById("document_dateInput").value;

//         if (!input) {
//             alert("Please select a quotation date");
//             return;
//         }

//         // Parse selected date
//         const document_date = new Date(input);

//         // Format document date as "12 Jan 2026"

//         // Calculate due date (+1 month)
//         const due_date = new Date(document_date);
//         due_date.setMonth(due_date.getMonth() + 1);

//         const formattedDocDate = document_date.toLocaleDateString("en-GB", {
//             day: "2-digit",
//             month: "short",
//             year: "numeric",
//         });
//         const formattedDueDate = due_date.toLocaleDateString("en-GB", {
//             day: "2-digit",
//             month: "short",
//             year: "numeric",
//         });

//         closeDatePromtModal();

//         // Pass formatted dates to your print function
//         if (quotationNextAction)
//             console.log("formattedDueDate", formattedDueDate);
//             quotationNextAction(formattedDocDate, formattedDueDate);
//     });
