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
            data-bar_code="${p.bar_code ?? ""}"
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
            data-category_id="${p.category_id ?? ""}"
            data-category_name="${p.category_name ?? ""}"
            data-unit="${p.unit ?? ""}"
            data-track_stock="${p.track_stock}"
            data-allow_discount="${p.allow_discount}"
            data-allow_return="${p.allow_return}"
            data-status="${p.status}"
            data-image="${p.image}"

            >

            <td><input type="radio" name="product_id" value="${p.id}"></td>
            <td>${p.id}</td>
            <td>
                  <img width="300px; padding:5px;" src="/assets/startic_img/${encodeURIComponent(p.image)}" alt="" />
            </td>
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
            <td>${p.category_name ?? "-"}</td>
                            <td>${p.category_id ?? "-"}</td>
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
        const formattedDocumentDate = document_date.toLocaleDateString(
            "en-GB",
            options,
        );
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
        const formattedDocumentDate = document_date.toLocaleDateString(
            "en-GB",
            options,
        );
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
                                <strong>Reciept for:</strong>
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
        const formattedDocumentDate = document_date.toLocaleDateString(
            "en-GB",
            options,
        );
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

function openDatePromt_Modal(onConfirm, amount) {
    const modal = document.getElementById("DatePromptModal");
    const dateInput = document.getElementById("document_dateInput");
    const validInput = document.getElementById("due_date");

    const total_amount = document.querySelector("#total_amount").value;
    const converted_total_amount = document.querySelector(
        "#converted_total_amount",
    ).value;

    const currency_display_name = document.querySelector(
        "#currency_display_name",
    );
    const currency_display_name2 = document.querySelector(
        "#currency_display_name2",
    );
    const currency_display_symbol = document.querySelector(
        "#currency_display_symbol",
    );
    const currency = document.querySelector("#currency_name");
    const currency_factor = document.querySelector("#currency_display_factor");
    const currency_factor_input = document.querySelector("#currency_factor");

    document.querySelector("#display_pay_amount").value = total_amount + " $";
    document.querySelector("#display_pay_amount_converted").value =
        converted_total_amount + " " + currency_display_symbol.value;
    currency_display_name.textContent = currency.value;
    currency_display_name2.textContent = currency.value;
    currency_factor_input.value = currency_factor.value;

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

    modal.querySelector("#confirmPayBtn").onclick = () => {
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

// Get Category on Click New
document
    .getElementById("btnAddProduct")
    .addEventListener("click", async function () {
        const select = document.getElementById("categorySelect");

        // Reset
        select.innerHTML = `<option value="">Loading categories...</option>`;

        try {
            const response = await fetch("/categories");
            const categories = await response.json();

            select.innerHTML = `<option value="">-- Select Category --</option>`;

            categories.forEach((cat) => {
                select.innerHTML += `
                <option value="${cat.id}">
                    ${cat.name}
                </option>
            `;
            });
        } catch (error) {
            console.error(error);
            select.innerHTML = `<option value="">Failed to load categories</option>`;
        }
    });
document
    .getElementById("productImage")
    .addEventListener("change", function (e) {
        const preview = document.getElementById("imagePreview");
        const file = e.target.files[0];

        if (!file) {
            preview.classList.add("hidden");
            preview.src = "";
            return;
        }

        if (!file.type.startsWith("image/")) {
            alert("Please select an image file");
            e.target.value = "";
            preview.classList.add("hidden");
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            preview.src = event.target.result;
            preview.classList.remove("hidden");
        };
        reader.readAsDataURL(file);
    });

// Submit New Product
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("AddProductForm");
    if (!form) return;
    const submitBtn = form.querySelector('button[type="submit"]');

    // Live image preview
    const imageInput = document.getElementById("productImage");
    const imagePreviewContainer = document.createElement("div");
    imagePreviewContainer.id = "imagePreview";
    imageInput.parentNode.appendChild(imagePreviewContainer);

    imageInput.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (!file) {
            imagePreviewContainer.innerHTML = "";
            return;
        }
        const reader = new FileReader();
        reader.onload = function (ev) {
            imagePreviewContainer.innerHTML = `<img src="${ev.target.result}" alt="Preview" class="mt-2 w-32 h-32 object-cover rounded" />`;
        };
        reader.readAsDataURL(file);
    });

    // Async form submit
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.innerText = "Saving...";

        try {
            const response = await fetch("/products/store", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'input[name="_token"]',
                    ).value,
                },
                body: new FormData(form),
            });

            let data;
            try {
                data = await response.json();
            } catch {
                data = {}; // in case response is not JSON
            }

            if (!response.ok) {
                // Show server message if exists, else fallback
                const message =
                    data.message ||
                    `Error ${response.status}: ${response.statusText}`;
                throw new Error(message);
            }

            // ✅ SUCCESS
            showToast({
                message: data.message || "Product added successfully",
                type: "success",
            });

            form.reset();
            imagePreviewContainer.innerHTML = "";

            document
                .querySelector('[data-modal-hide="default-modal-add-product"]')
                ?.click();
        } catch (err) {
            // Always show toast
            showToast({
                message: err.message || "Server error. Please try again.",
                type: "error",
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = "Save Product";
        }
    });
});

// Hook Edit button
document.getElementById("btnEditProduct").addEventListener("click", () => {
    openUpdateProductModal();
});

let selectedProductId = null;

// Get ID
function getSelectedProductId() {
    const selected = document.querySelector('input[name="product_id"]:checked');
    selectedProductId = selected ? selected.value : null; // store it
    return selectedProductId;
}
async function openUpdateProductModal() {
    const selected = document.querySelector('input[name="product_id"]:checked');

    if (!selected) {
        showToast({
            message: "Please select a product first",
            type: "warning",
        });
        return;
    }

    const productId = selected.value;
    const row = document.querySelector(`tr[data-id="${productId}"]`);
    if (!row) return;

    // Load categories
    let categories = [];
    try {
        const response = await fetch("/categories");
        categories = await response.json(); // e.g., [{id:1, name:'APPETIZER'}, ...]
    } catch (error) {
        console.error("Failed to load categories:", error);
    }

    // Take ID from Modal
    const categorySelect = document.getElementById("prod-category");

    categorySelect.innerHTML = ""; // clear previous options

    const currentCategoryId = row.getAttribute("data-category_id") || "";

    // Check if current category exists in the categories list
    const currentCategoryExists = categories.some(
        (cat) => String(cat.id) === currentCategoryId,
    );

    if (currentCategoryExists) {
        // Render all categories with the current one selected
        categories.forEach((cat) => {
            const option = document.createElement("option");
            option.value = cat.id;
            option.textContent = cat.name;
            if (String(cat.id) === currentCategoryId) option.selected = true;
            categorySelect.appendChild(option);
        });
    } else {
        // Current category not found → add placeholder with previous category name
        const placeholder = document.createElement("option");
        placeholder.value = currentCategoryId;
        placeholder.textContent = row.getAttribute("data-category_name") || "";
        placeholder.selected = true;
        categorySelect.appendChild(placeholder);

        // Then add all categories normally
        categories.forEach((cat) => {
            const option = document.createElement("option");
            option.value = cat.id;
            option.textContent = cat.name;
            categorySelect.appendChild(option);
        });
    }

    document.getElementById("preivew_img").src =
        `/assets/startic_img/${encodeURIComponent(row.dataset.image)}`;

    const sellPrice = parseFloat(row.dataset.sell_price) || 0; // Selling price
    const vat = parseFloat(row.dataset.vat) || 0; // VAT %
    const discount = parseFloat(row.dataset.discount_percent) || 0; // Discount %

    const priceAfterDiscount = sellPrice - (sellPrice * discount) / 100;
    const finalPrice = priceAfterDiscount - (priceAfterDiscount * vat) / 100;
    // ID
    document.getElementById("prod-id").value = productId;

    // BASIC
    document.getElementById("prod-code").value = row.dataset.code ?? "";
    document.getElementById("prod-barcode").value = row.dataset.bar_code ?? "";
    document.getElementById("prod-name").value = row.dataset.name ?? "";
    document.getElementById("prod-variant").value = row.dataset.variant ?? "";
    document.getElementById("prod-description").value =
        row.dataset.description ?? "";

    document.getElementById("prod-price-final").value = finalPrice.toFixed(3);

    // console.log(row.dataset.category_id);
    // CATEGORY / UNIT
    // document.getElementById("hidden_category_id").value =
    //     row.dataset.category_id ?? "";

    document.getElementById("prod-unit").value = row.dataset.unit ?? "";

    // STOCK
    document.getElementById("prod-min-stock").value =
        row.dataset.min_stock ?? 0;
    document.getElementById("prod-max-stock").value =
        row.dataset.max_stock ?? 0;

    // PRICE
    document.getElementById("prod-cost").value = row.dataset.cost ?? 0;
    document.getElementById("prod-price").value = row.dataset.sell_price ?? 0;
    document.getElementById("prod-vat").value = row.dataset.vat ?? 0;
    document.getElementById("prod-discount").value =
        row.dataset.discount_percent ?? 0;

    // CHECKBOXES / STATUS
    document.getElementById("prod-status").checked =
        row.dataset.status == "true";
    document.getElementById("prod-category_name").value =
        row.dataset.category_name ?? "";

    document.getElementById("prod-track-stock").checked =
        row.dataset.track_stock === "true";

    document.getElementById("prod-allow-discount").checked =
        row.dataset.allow_discount === "true";

    let discountInput = document.getElementById("prod-discount");
    if (row.dataset.allow_discount === "true") {
        discountInput.disabled = false; // enable
    } else {
        discountInput.disabled = true; // disable
    }

    document.getElementById("prod-allow-return").checked =
        row.dataset.allow_return === "true";

    // SHOW MODAL
    document
        .getElementById("confirm-update-product")
        .classList.remove("hidden");
}

function closeUpdateProductModal() {
    const modal = document.getElementById("confirm-update-product");
    if (modal) {
        modal.classList.add("hidden");
    }
}

async function confirmUpdateProduct() {
    const id = document.getElementById("prod-id").value;

    const data = {
        bar_code: document.getElementById("prod-barcode").value,
        code: document.getElementById("prod-code").value,
        name: document.getElementById("prod-name").value,
        variant: document.getElementById("prod-variant").value,
        description: document.getElementById("prod-description").value,

        min_stock: document.getElementById("prod-min-stock").value,
        max_stock: document.getElementById("prod-max-stock").value,
        cost: document.getElementById("prod-cost").value,
        sell_price: document.getElementById("prod-price").value,
        vat: document.getElementById("prod-vat").value,
        discount: document.getElementById("prod-discount").value,

        category_id: document.getElementById("prod-category").value,
        category_name: document.getElementById("prod-category_name").value,
        unit: document.getElementById("prod-unit").value,

        track_stock: document.getElementById("prod-track-stock").checked,
        allow_discount: document.getElementById("prod-allow-discount").checked,
        allow_return: document.getElementById("prod-allow-return").checked,
        status: document.getElementById("prod-status").checked,
    };

    try {
        const res = await fetch(`/product/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document.querySelector("input[name=_token]").value,
            },
            body: JSON.stringify(data),
        });

        const result = await res.json();

        if (result.success) {
            loadProducts(1);
            closeUpdateProductModal();

            showToast({
                message: "Product updated successfully",
                type: "success",
            });
        } else {
            alert("❌ Update failed");
        }
    } catch (err) {
        console.error(err);
        alert("❌ Server error");
    }
}
function calculateFinalPrice() {
    const priceInput = document.getElementById("prod-price");
    const vatInput = document.getElementById("prod-vat");
    const discountInput = document.getElementById("prod-discount");

    const price = parseFloat(priceInput.value) || 0;
    let vat = parseFloat(vatInput.value) || 0;
    let discount = parseFloat(discountInput.value) || 0;

    // limit VAT to 30%
    if (vat > 30) {
        vat = 30;
        vatInput.value = 30;
    }
    if (vat < 0) {
        vat = 0;
        vatInput.value = 0;
    }

    // limit Discount to 100%
    if (discount > 100) {
        discount = 100;
        discountInput.value = 100;
    }
    if (discount < 0) {
        discount = 0;
        discountInput.value = 0;
    }

    const vatAmount = price * (vat / 100);
    const discountAmount = price * (discount / 100);

    let finalPrice = price + vatAmount - discountAmount;

    // prevent negative sell price
    finalPrice = Math.max(finalPrice, 0);

    document.getElementById("prod-price-final").value = finalPrice.toFixed(2);
}

// auto recalc on typing
["prod-price", "prod-vat", "prod-discount"].forEach((id) => {
    document.getElementById(id).addEventListener("input", calculateFinalPrice);
});

document
    .getElementById("openTableModal")
    .addEventListener("click", openTableModal);

async function openTableModal() {
    const tbody = document.getElementById("Table-table-body");
    tbody.innerHTML =
        '<tr><td colspan="5" class="text-center py-2">Loading...</td></tr>';

    try {
        const res = await fetch("/tables"); // Your API route
        if (!res.ok) throw new Error("Failed to fetch tables");

        const tables = await res.json();

        tbody.innerHTML = ""; // Clear loading

        tables.forEach((table) => {
            const tr = document.createElement("tr");

            tr.innerHTML = `
                <td class="px-4 py-2 text-center">
                    <input type="radio" name="table_id" value="${table.id}">
                </td>
                <td class="px-4 py-2">${table.id}</td>
                <td class="px-4 py-2">${table.name}</td>
              <td class="px-4 py-2">${table.status ? "Occupied" : "Available"}</td>

            `;

            tbody.appendChild(tr);
        });
    } catch (err) {
        console.error(err);
        tbody.innerHTML =
            '<tr><td colspan="5" class="text-center py-2 text-red-500">Failed to load tables</td></tr>';
    }
}

let cart_qty = 0;
let current_id = null;

async function showTableModal(qty_cart, id) {
    cart_qty = qty_cart;

    const modal = document.getElementById("default-modal-table-select-list");
    if (modal) modal.classList.remove("hidden");

    const tbody = document.getElementById("table-modal-body");
    tbody.innerHTML = `<tr><td colspan="4" class="text-center p-4">Loading...</td></tr>`;

    try {
        const response = await fetch("/tables");
        if (!response.ok) throw new Error("Network error fetching tables");

        const tables = await response.json();

        // Filter rows
        const tablesToShow =
            id === "ALL" ? tables : tables.filter((table) => table.id == id);

        tbody.innerHTML = "";

        tablesToShow.forEach((table) => {
            const tr = document.createElement("tr");

            const isOccupied = table.products && table.products.length > 0;

            const statusText = isOccupied ? "Occupied" : "Available";
            const statusClass = isOccupied
                ? "text-red-600 font-semibold"
                : "text-green-600 font-semibold";

            tr.innerHTML = `
                <td>${table.id}</td>
                <td>${table.name}</td>
                <td class="${statusClass}">${statusText}</td>
                <td></td>
            `;

            const td = tr.querySelector("td:last-child");
            td.style.display = "flex";
            td.style.gap = "0.5rem";

            /* =====================
               ADD ITEM BUTTON
            ===================== */
            const addButton = document.createElement("button");
            addButton.textContent = "Place Order";

            // 🔥 BLOCK LOGIC BASED ON MODE
            let blockAdd = false;
            let bockAdd_occupied = false;
            if (id === "ALL") {
                // ALL tables view → block any occupied table
                blockAdd = isOccupied;
                bockAdd_occupied = isOccupied;
            } else {
                // Specific table view → allow only current table if occupied
                blockAdd = isOccupied && table.id !== current_id;
                bockAdd_occupied = isOccupied;
            }

            if (blockAdd) {
                addButton.disabled = true;
                addButton.className =
                    "bg-gray-400 text-white px-3 py-1 rounded cursor-not-allowed";
                addButton.title = "This table is occupied";
            } else {
                addButton.className =
                    "bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded";

                addButton.addEventListener("click", () => {
                    selectTable(table.id);
                });
            }

            /* =====================
               LOAD BUTTON
            ===================== */
            const loadButton = document.createElement("button");
            loadButton.textContent = "Load Order";
            loadButton.className =
                "bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded";

            const PayButton = document.createElement("button");
            PayButton.textContent = "Payment";
            PayButton.className =
                "bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded";
            if (bockAdd_occupied) {
                PayButton.className =
                    "bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded";

                PayButton.addEventListener("click", () => {
                    // 🔥 SET CURRENT TABLE HERE
                    current_id = table.id;
                    if (id === "ALL" && cart_qty > 0) {
                        showToast({
                            message:
                                "Current cart has items. Cannot load all tables.",
                            type: "error",
                        });
                        return;
                    }
                    table_pay(table.id);
                });
            } else {
                PayButton.disabled = true;
                PayButton.className =
                    "bg-gray-400 text-white px-3 py-1 rounded cursor-not-allowed";
                PayButton.title = "This table is occupied";
            }

            loadButton.addEventListener("click", () => {
                // 🔥 SET CURRENT TABLE HERE
                current_id = table.id;

                if (id === "ALL" && cart_qty > 0) {
                    showToast({
                        message:
                            "Current cart has items. Cannot load all tables.",
                        type: "error",
                    });
                    return;
                }

                LoadTable_product(table.id);

                // Disable all Load buttons
                modal.querySelectorAll("button").forEach((btn) => {
                    if (btn.textContent === "Check Out") {
                        btn.disabled = true;
                        btn.classList.add("bg-gray-400", "cursor-not-allowed");
                    }
                });
            });

            td.appendChild(addButton);
            td.appendChild(loadButton);
            td.appendChild(PayButton);

            tbody.appendChild(tr);
        });

        // No tables found
        if (tablesToShow.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No tables found</td></tr>`;
        }
    } catch (error) {
        console.error("Error loading tables:", error);
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-red-600 p-4">Failed to load tables</td></tr>`;
        showToast({
            message: "Failed to load tables. See console for details.",
            type: "error",
        });
    }
}

function selectTable(tableId) {
    Livewire.dispatch("transferCartToTable", {
        payload: { table_id: tableId },
    });

    // Hide modal after adding
    const modal = document.getElementById("default-modal-table-select-list");
    if (modal) modal.classList.add("hidden");
}

function LoadTable_product(tableId) {
    Livewire.dispatch("loadTableToCart", { table_id: tableId });

    // Hide modal after loading
    const modal = document.getElementById("default-modal-table-select-list");
    if (modal) modal.classList.add("hidden");
}
function exit_table() {
    Livewire.dispatch("exit_table");

    // Hide modal after loading
    showToast({
        message: `Exit Table Editing Mode.`,
        type: "success",
    });
    const modal = document.getElementById("default-modal-table-select-list");
    if (modal) modal.classList.add("hidden");
}
function table_pay(id) {
    // close modal
    const modal = document.getElementById("default-modal-table-select-list");
    if (modal) modal.classList.add("hidden");
    // load to cart

    Livewire.dispatch("loadTableToCartPayment", { table_id: id });
}



const displayUSD = document.getElementById("total_amount"); // total amount USD
const payUSDInput = document.getElementById("pay_usd");
const payOtherInput = document.getElementById("pay_other");
const currencyFactorInput = document.getElementById("currency_display_factor");
const returnedInput = document.getElementById("returned_amount");
const returnedInputOther = document.getElementById("returned_amount_other");
const confirmPayBtn = document.getElementById("confirmPayBtn");

function formatCurrency(value, symbol) {
    return `${value} ${symbol}`;
}

function updatePayment() {
    const totalAmountUSD = parseFloat(displayUSD.value) || 0;

    // Get numeric values from inputs
    const payUSD = payUSDInput.value;
    const payOther = payOtherInput.value;
    const factor = parseFloat(currencyFactorInput.value) || 1;
    const corrency_other_symbol = document.querySelector(
        "#currency_display_symbol",
    ).value;
    // Convert other currency to USD
    const payOtherInUSD = payOther / factor;

    const totalPaidUSD = parseFloat(payUSD) + parseFloat(payOtherInUSD);
    let returnedUSD = 0;
    let returnedOther = 0;
    // Calculate returned
    returnedUSD = totalPaidUSD - totalAmountUSD;
    returnedOther = returnedUSD * factor;

    // Update inputs with formatted value
    if (returnedUSD < 0) {
        returnedUSD = 0;
    }
    if (returnedOther < 0) {
        returnedOther = 0;
    }
    returnedInput.value = formatCurrency(returnedUSD.toFixed(2), "$");
    returnedInputOther.value = formatCurrency(
        returnedOther.toFixed(0),
        corrency_other_symbol,
    );

    // Update input formatting while typing
    payUSDInput.value = payUSD;

    payOtherInput.value = payOther;

    // Highlight
    const isEnough = totalPaidUSD >= totalAmountUSD;

    if (isEnough) {
        // enable button
        confirmPayBtn.disabled = false;
        confirmPayBtn.textContent = "Confirm Payment";

        confirmPayBtn.classList.remove("bg-gray-400", "cursor-not-allowed");
        confirmPayBtn.classList.add(
            "bg-emerald-600",
            "hover:bg-emerald-700",
            "cursor-pointer",
        );
    } else {
        // disable button
        confirmPayBtn.disabled = true;
        confirmPayBtn.textContent = "Enter Amount";

        confirmPayBtn.classList.remove(
            "bg-emerald-600",
            "hover:bg-emerald-700",
            "cursor-pointer",
        );
        confirmPayBtn.classList.add("bg-gray-400", "cursor-not-allowed");
    }
}

// Attach events
payUSDInput.addEventListener("input", updatePayment);
payOtherInput.addEventListener("input", updatePayment);

// Initialize
updatePayment();


window.addEventListener("cart-loaded", (e) => {
    console.log("Table cart loaded!", e.detail);
    document.querySelector("#count_cart_input").value = 1;
    print("Receipt");
});

window.addEventListener("serve-table", (e) => {
    showToast({
        message: `Served ${e.detail[0].name} table success.`,
        type: "success",
    });
});
