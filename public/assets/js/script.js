// DEVELOP BY Y SAMOUN IT EXECUTIVE
// DEVELOP AT 2025-2026
// ASSISTED BY CHAT GPT

// INFO BEFORE BEGIN

// LIST =  DATA FETCH AND RENDER

// ADD = CREATE NEW OBJECT

// UPDATE = UPDATE EXISTING OBJECT

// DELETE = DELETE CURRENT OBJECT

// OBJECT  -->
// PRODUCT  // CUSTOMER // CURRENCY  // WAREHOUSE // WAREHOUSE PRODUCT // TABLE AND QUOTE  // TABLE PRODUCT

// ADD CUSTOMER
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("AddcustomerForm");
    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.innerText = "Saving...";

        try {
            const response = await fetch("/customers/store", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                },
                body: new FormData(form),
            });

            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            // ✅ SUCCESS
            showToast({
                message: data.message || "Customer created successfully",
                type: "success",
            });
            loadCustomers(1);
            form.reset();

            document
                .querySelector('[data-modal-hide="default-modal-customer"]')
                ?.click();
        } catch (err) {
            // ❌ VALIDATION ERRORS
            if (err.errors) {
                Object.values(err.errors).forEach((msgs) => {
                    showToast({
                        message: msgs[0],
                        type: "error",
                    });
                });
            } else {
                showToast({
                    message: "Server error. Please try again.",
                    type: "error",
                });
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = "Save Customer";
        }
    });
});

// GLOBAL VARIABLE ID CUSTOMER SELETED  FOR UPDATE DELETE
let selectedCustomerId = null;
// UPDATE CUSTOMER
function getSelectedCustomerId() {
    const selected = document.querySelector(
        'input[name="customer_id"]:checked',
    );
    selectedCustomerId = selected ? selected.value : null; // store it
    return selectedCustomerId;
}

// UPDATE CUSTOMER

document.getElementById("btnEditCustomer").addEventListener("click", () => {
    openUpdateCustModal();
});

function closeDeleteCustModal() {
    document.getElementById("confirm-delete-cust").classList.add("hidden");
}

// UPDATE CUSTOMER
// Hook CLOSE button
document.getElementById("btnEditCustomer").addEventListener("click", () => {
    openUpdateCustModal();
});

// UPDATE CUSTOMER
function openUpdateCustModal() {
    const customerId = getSelectedCustomerId();
    if (!customerId) {
        showToast({
            message: "Please select a customer first",
            type: "error",
        });
        return;
    }

    // Get the selected row
    const row = document.querySelector(`tr[data-id="${customerId}"]`);
    console.log(row.dataset);
    // Read data directly from data attributes
    document.getElementById("cust-customer_code").value =
        row.dataset.customer_code ?? "";
    document.getElementById("cust-name").value = row.dataset.name ?? "";
    document.getElementById("cust-phone").value = row.dataset.phone ?? "";

    document.getElementById("cust-email").value = row.dataset.email ?? "";
    document.getElementById("cust-address1").value = row.dataset.address1 ?? "";
    document.getElementById("cust-address2").value = row.dataset.address2 ?? "";
    document.getElementById("cust-contact").value =
        row.dataset.contact_name ?? "";

    document.getElementById("cust-contact_phone").value =
        row.dataset.contact_phone ?? "";
    document.getElementById("cust-city").value = row.dataset.city ?? "";
    document.getElementById("cust-country").value = row.dataset.country ?? "";
    document.getElementById("cust-type").value = row.dataset.type ?? "";
    document.getElementById("cust-discount_percent").value = parseFloat(
        row.dataset.discount_percent ?? 0,
    ).toFixed(2);

    document.getElementById("cust-point").value = row.dataset.point ?? 0;
    document.getElementById("cust-status").value = row.dataset.status ?? "1";

    // Show modal
    document.getElementById("confirm-update-cust").classList.remove("hidden");
}
// UPDATE CUSTOMER
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
function closeUpdateCustModal() {
    document.getElementById("confirm-update-cust").classList.add("hidden");
}

// ADD CURRENCY
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

// Close modal SAVE AND UNSAVE
cancelBtn.addEventListener("click", () => {
    unsaveModal.classList.add("hidden");
});

// Confirm refresh
continueBtn.addEventListener("click", () => {
    unsaveModal.classList.add("hidden");
    location.reload(); // actually refresh the page
});

// GLOBAL TOAST
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
// GLOBAL HIDE TOAST
function hideToast() {
    const toast = document.getElementById("toastMessage");
    toast.classList.add("hidden");

    // Optional: reset icon and text
    document.getElementById("toastText").innerText = "";
    document.getElementById("toastIcon").innerText = "✔️";
}
// DELETE CUSTOMER
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

// LIST CUSTOMER
const searchInput = document.getElementById("customerSearchInput");
const typeSelect = document.getElementById("customerTypeSelect");
const activeCheckbox = document.getElementById("customerSearchCheckbox");
const tbody = document.getElementById("customer-table-body");
const customerSearch = document.getElementById("customerSearch");

if (customerSearch) {
    customerSearch.addEventListener("input", function () {
        if (this.value.trim() === "") {
            Livewire.dispatch("resetCustomer");

            // also clear modal customer fields
            document.getElementById("so_customer_id_info").value = "";
            document.getElementById("so_customer_name_info").value =
                "Walk-in Customer";
            document.getElementById("so_customer_phone_info").value = "";
            document.getElementById("so_customer_address_info").value = "";
        }
    });
}

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
                <td colspan="12" class="px-4 py-4 text-center text-rose-500">
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
             data-name ="${c.name ?? ""}"
            data-address1="${c.address1 ?? ""}"
            data-city="${c.city ?? ""}"
            data-country="${c.country ?? ""}"
            data-discount_percent="${c.discount_percent ?? 0}"
            data-point="${c.point ?? 0}"
            data-type="${c.type}"
            data-address2="${c.address2}"
            data-contact_name="${c.contact_name ?? ""}"
            data-phone="${c.phone}"
            data-email="${c.email}"
            data-contact_phone="${c.contact_phone}"
            data-status="${c.status}">
            <td><input type="radio" name="customer_id" value="${c.id}"></td>
            <td>${c.id}</td>
            <td>${c.customer_code ?? "-"}</td>
            <td>${c.name}</td>
            <td>${c.address1}</td>
            <td>${c.phone ?? "-"}</td>
            <td>${c.email ?? "-"}</td>
            <td>${c.type}</td>
            <td>${parseFloat(c.discount_percent).toFixed(2)}</td>

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
let allProducts = [];
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
    allProducts = result.data; // 🔥 store full data
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
                <div class="img">
                          <img src="/assets/startic_img/${encodeURIComponent(p.image)}" alt="" />
                </div>

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
            <td>${p.category_id ?? "-"}</td>
            <td>${p.category?.name ?? "-"}</td>
            <td>${p.unit ?? "-"}</td>
            <td>${p.category_name ?? "-"}</td>

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
document.addEventListener("click", function (e) {
    const row = e.target.closest("tr[data-id]");
    if (!row) return;

    // Ignore clicks on inputs themselves (optional safety)
    if (e.target.tagName === "INPUT") return;

    // Select radio
    const radio = row.querySelector('input[type="radio"]');
    if (radio) {
        radio.checked = true;
        radio.dispatchEvent(new Event("change", { bubbles: true }));
    }

    // Call your edit logic
    // editProductFromRow(row);
});

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

let wh;
// Load Warehouse for select Stock
async function loadWarehouses() {
    const select = document.getElementById("warehouseTypeSelect");

    // Reset dropdown
    select.innerHTML = `
        <option value="All">All Warehouse</option>
    `;

    try {
        const response = await fetch("/warehouses/list");
        if (!response.ok) throw new Error("Fetch failed");

        const warehouses = await response.json();
        console.log(warehouses); // check your data

        if (!warehouses.length) return; // no warehouses
        wh = warehouses;
        warehouses.forEach((w) => {
            select.insertAdjacentHTML(
                "beforeend",
                `<option value="${w.id}">${w.name}${w.location ? " - " + w.location : ""}</option>`,
            );
        });
    } catch (err) {
        console.error(err);

        select.innerHTML = `
            <option value="All">All Warehouse</option>
            <option disabled>Failed to load warehouses</option>
        `;
    }
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

let currentSort = {
    by: "expire", // default sort column
    dir: "asc", // default direction
};

async function loadCategories_product() {
    try {
        const res = await fetch("/product/categories");
        const categories = await res.json();

        const select = document.getElementById("category-filter2");
        select.innerHTML = '<option value="">All Categories</option>'; // Clear existing options
        categories.forEach((category) => {
            const option = document.createElement("option");
            option.value = category.id;
            option.textContent = category.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading categories:", error);
    }
}
document
    .getElementById("openWarehouseModel")
    .addEventListener("click", function () {
        // Load WARE ID In Select
        loadWarehouses();
        loadCategories_product();
        // Fetch and Render Stock
        loadWarehouseStock(0, 1); // or handle All case
    });
document
    .getElementById("warehouseTypeSelect")
    .addEventListener("change", function () {
        const warehouseId = this.value;

        if (warehouseId === "All") {
            loadWarehouseStock(0, 1); // or handle All case
        } else {
            loadWarehouseStock(warehouseId, 1);
        }
    });
// Listen to filter inputs (search, variant, status, stock)
document
    .querySelectorAll(
        "#limit-filter, #search-stock, #status-filter, #stock-filter, #category-filter2",
    )
    .forEach((el) => {
        el.addEventListener("input", () => {
            loadWarehouseStock(currentWarehouseId);
        });
    });
const modal = document.getElementById("warehouse-stock-modal");
const tbody_stock = document.getElementById("warehouse-stock-tbody");
const closeBtn = document.getElementById("close-modal");
const searchInput_stock = document.getElementById("search-stock");
const statusFilter = document.getElementById("status-filter");
const LimitFilter = document.getElementById("limit-filter");
const Category = document.getElementById("category-filter2");

async function loadWarehouseStock(warehouseId, page = 1) {
    try {
        currentWarehouseId = warehouseId;

        tbody_stock.innerHTML = `
                <tr>
                    <td colspan="17" class="px-4 py-4 text-center text-rose-500">
                        Loading...
                    </td>
                </tr>
            `;

        // Only include filters if not empty
        const params = new URLSearchParams();
        const limit = LimitFilter.value.trim();

        const search = searchInput_stock.value.trim();
        const category_id = Category.value.trim();
        const status = statusFilter.value;
        const stock = document.getElementById("stock-filter").value;

        if (search) params.append("search", search);
        if (limit) params.append("limit", limit);
        if (status !== "") params.append("status", status);
        if (stock) params.append("stock", stock);
        if (category_id) params.append("category_id", category_id);

        params.append("page", page);

        const res = await fetch(
            `/warehouses/${warehouseId}/stock?${params.toString()}`,
        );

        const result = await res.json();

        renderStockTable(result.data, result.current_page, result.per_page);
        renderPagination(result);
    } catch (err) {
        console.error(err);
        alert("Error fetching stock");
    }
}
function renderStockPagination(result) {
    const container = document.getElementById("paginationContainer_stock");
    const pageInfo = document.getElementById("pageInfo_stock");

    if (!container) return;

    container.innerHTML = "";

    if (result.per_page === "All") {
        if (pageInfo) {
            pageInfo.textContent = `Showing all ${result.total} items`;
        }
        return;
    }

    const currentPage = result.current_page;
    const lastPage = result.last_page;

    if (pageInfo) {
        pageInfo.textContent = `Page ${currentPage} of ${lastPage} | Total ${result.total}`;
    }

    // Prev
    const prevBtn = document.createElement("button");
    prevBtn.innerHTML = `<i class="fa-solid fa-chevron-left"></i>`;
    prevBtn.disabled = currentPage <= 1;
    prevBtn.className = stockPaginationBtnClass(prevBtn.disabled);
    prevBtn.onclick = () =>
        loadWarehouseStock(currentWarehouseId, currentPage - 1);
    container.appendChild(prevBtn);

    // Pages
    for (let i = 1; i <= lastPage; i++) {
        if (i === 1 || i === lastPage || Math.abs(i - currentPage) <= 2) {
            const btn = document.createElement("button");
            btn.textContent = i;
            btn.className =
                i === currentPage
                    ? "px-3 py-1 rounded-lg bg-blue-600 text-white text-sm"
                    : "px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm";

            btn.onclick = () => loadWarehouseStock(currentWarehouseId, i);
            container.appendChild(btn);
        }
    }

    // Next
    const nextBtn = document.createElement("button");
    nextBtn.innerHTML = `<i class="fa-solid fa-chevron-right"></i>`;
    nextBtn.disabled = currentPage >= lastPage;
    nextBtn.className = stockPaginationBtnClass(nextBtn.disabled);
    nextBtn.onclick = () =>
        loadWarehouseStock(currentWarehouseId, currentPage + 1);
    container.appendChild(nextBtn);
}

function stockPaginationBtnClass(disabled) {
    return disabled
        ? "px-3 py-1 rounded-lg bg-gray-100 text-gray-400 text-sm cursor-not-allowed"
        : "px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm";
}
function renderStockTable(products, currentPage = 1, perPage = 10) {
    tbody_stock.innerHTML = "";

    if (products.length === 0) {
        tbody_stock.innerHTML = `
            <tr>
                <td colspan="17" class="text-center py-4 text-rose-500">
                    No data found
                </td>
            </tr>
        `;
        return;
    }

    products.forEach((p, index) => {
        // Proper row number with pagination
        const rowNumber = (currentPage - 1) * perPage + index + 1;

        let expireText = "N/A";
        if (p.expire) {
            const d = new Date(p.expire);
            const day = String(d.getDate()).padStart(2, "0");
            const month = String(d.getMonth() + 1).padStart(2, "0");
            const year = d.getFullYear();
            expireText = `${day}/${month}/${year}`;
        }
        const warehouseColors = [
            "#EF4444",
            "#F97316",
            "#F59E0B",
            "#10B981",
            "#06B6D4",
            "#3B82F6",
            "#6366F1",
            "#8B5CF6",
            "#EC4899",
            "#14B8A6",
        ];
        let color = warehouseColors[p.warehouse_id % warehouseColors.length];
        tbody_stock.insertAdjacentHTML(
            "beforeend",
            `
            <tr  id="transfer_row-${rowNumber}" class="hover:bg-green-50 cursor-pointer transition-colors">
                <td class="px-3 text-left text-sm text-gray-600">${rowNumber}</td>


                <td class="px-3 text-left text-sm">${p.code ?? ""}</td>
                <td class="px-3 text-left text-sm font-medium">${p.product_name}</td>
                <td class="px-3 text-left text-sm">${p.variant ?? ""}</td>
                <td class="px-3 text-left text-sm">${p.description ?? ""}</td>
                <td class="px-3 text-left text-sm">${p.lot}</td>
                <td class="px-3 text-left text-sm">${expireText}</td>
                <td class="px-3 text-center text-sm font-bold">${p.qty}</td>
                <td class="px-3  text-sm">${p.unit}</td>
                <td class="px-3 text-right text-sm">${p.cost_price ?? 0}</td>
                <td class="px-3 text-right text-sm">${p.vat ?? 0}</td>
                <td class="px-3 text-right text-sm">${Number(p.sell_price ?? 0).toFixed(2)}</td>
                <td class="px-3 text-right text-sm">${Number(p.sell_price_vat ?? 0).toFixed(2)}</td>
                <td class="px-3 text-left text-sm">${p.category_name ?? "NA"}</td>
                       <td class="px-3 text-left text-sm">
    <span class="px-2 py-1 rounded-full text-xs font-medium"
        style="background:${color}20; color:${color}; border:1px solid ${color}50;">
        ${p.warehouse_name ?? "NA"}
    </span>
</td>

                <td class="px-3      text-sm ${p.status ? "text-green-600" : "text-red-500"}">
                    ${p.status ? "Active" : "Inactive"}
                </td>
            <td class="px-3 text-sm">

                ${p.qty > 0 ? `<button onclick="openLotModal_transfer( ${rowNumber},${p.lot_id})" class="px-5 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition mt-2"><i class="fa-classic fa-solid fa-arrow-right-arrow-left"></i></button>` : ""}
            </td>
            </tr>
            `,
        );
    });
}

function renderPagination(result) {
    const container = document.getElementById("paginationContainer_stock");
    container.innerHTML = "";

    if (!result.last_page || result.last_page <= 1) return;

    const currentPage = result.current_page;
    const lastPage = result.last_page;

    // Previous Button
    if (currentPage > 1) {
        container.insertAdjacentHTML(
            "beforeend",
            `
            <button class="px-3 py-1 border rounded hover:bg-gray-100"
                onclick="loadWarehouseStock(currentWarehouseId, ${currentPage - 1})">
                Prev
            </button>
        `,
        );
    }

    // Page Numbers
    for (let i = 1; i <= lastPage; i++) {
        container.insertAdjacentHTML(
            "beforeend",
            `
            <button
                class="px-3 py-1 border rounded
                ${i === currentPage ? "bg-blue-500 text-white" : "hover:bg-gray-100"}"
                onclick="loadWarehouseStock(currentWarehouseId, ${i})">
                ${i}
            </button>
        `,
        );
    }

    // Next Button
    if (currentPage < lastPage) {
        container.insertAdjacentHTML(
            "beforeend",
            `
            <button class="px-3 py-1 border rounded hover:bg-gray-100"
                onclick="loadWarehouseStock(currentWarehouseId, ${currentPage + 1})">
                Next
            </button>
        `,
        );
    }
}

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

// ADD Product
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
            loadProducts(1);
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

// Get ID Product
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
    const categorySelect = document.getElementById("prod-category-id");

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

    document.getElementById("preview_img").src =
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

    document.getElementById("sell_price-final").value = finalPrice.toFixed(3);

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

    document.getElementById("prod-sell-price").value =
        row.dataset.sell_price ?? 0;
    document.getElementById("prod-vat").value = row.dataset.vat ?? 0;
    document.getElementById("prod-discount").value =
        row.dataset.discount_percent ?? 0;

    // CHECKBOXES / STATUS
    document.getElementById("prod-status").checked =
        row.dataset.status == "true";
    document.getElementById("prod-category-name").value =
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

    try {
        // 1️⃣ Create FormData
        const formData = new FormData();
        formData.append("_method", "PUT"); // Laravel fake PUT

        // 2️⃣ Define field mappings
        const fields = [
            "barcode",
            "code",
            "name",
            "variant",
            "description",
            "min_stock",
            "max_stock",
            "cost",
            "sell_price",
            "vat",
            "discount",
            "category_id",
            "category_name",
            "unit",
        ];

        // 3️⃣ Append text/number fields
        fields.forEach((field) => {
            const el = document.getElementById(
                `prod-${field.replace("_", "-")}`,
            );
            if (el) formData.append(field, el.value ?? "");
            console.log(field);
            console.log(el);
        });

        // 4️⃣ Append switch/checkbox fields
        const switches = [
            "track_stock",
            "allow_discount",
            "allow_return",
            "status",
        ];

        switches.forEach((field) => {
            const el = document.getElementById(
                `prod-${field.replace("_", "-")}`,
            );
            if (el) formData.append(field, el.checked ? 1 : 0);
        });

        // 5️⃣ Append image if selected
        const imageFile = document.getElementById("update_image")?.files[0];
        if (imageFile) formData.append("image", imageFile);

        // 6️⃣ Debug: log FormData
        console.log("FormData entries:");
        for (const pair of formData.entries()) {
            console.log(pair[0], pair[1]);
        }

        // 7️⃣ Send request
        const res = await fetch(`/product/${id}`, {
            method: "POST", // Must be POST for Laravel FormData + _method=PUT
            headers: {
                "X-CSRF-TOKEN":
                    document.querySelector("input[name=_token]").value,
            },
            body: formData,
        });

        const result = await res.json();

        // 8️⃣ Debug: log server response
        console.log("Server response:", result);

        // 9️⃣ Success handling
        if (result.success) {
            loadProducts(1);
            closeUpdateProductModal();
            showToast({
                message: "Product updated successfully",
                type: "success",
            });
        } else {
            showToast({
                message: result.message || "Update failed",
                type: "error",
            });
        }
    } catch (err) {
        console.error("Server error:", err);
        showToast({
            message: "Server error while updating product",
            type: "error",
        });
    }
}

function calculateFinalPrice() {
    const priceInput = document.getElementById("prod-sell-price");
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

    document.getElementById("sell_price-final").value = finalPrice.toFixed(2);
}

// auto recalc on typing
["prod-sell-price", "prod-vat", "prod-discount"].forEach((id) => {
    document.getElementById(id).addEventListener("input", calculateFinalPrice);
});

// Initialize

const fileInput = document.getElementById("update_image");
const previewImg = document.getElementById("preview_img");

fileInput.addEventListener("change", function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        previewImg.src = ""; // Reset if no file selected
    }
});

function openModal() {
    document.getElementById("tableModal").classList.remove("hidden");
    document.getElementById("tableModal").classList.add("flex");
}

function closeModal() {
    document.getElementById("tableModal").classList.add("hidden");
    document.getElementById("tableModal").classList.remove("flex");
}

async function saveTable() {
    const name = document.getElementById("table_name").value.trim();

    if (!name) {
        alert("Table name is required");
        return;
    }

    try {
        const response = await fetch("/restaurant-tables/store", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                    .value,
                Accept: "application/json",
                "Content-Type": "application/json", // 🔥 must have
            },
            body: JSON.stringify({
                name: name,
            }),
        });

        const data = await response.json();

        if (data.success) {
            showToast({
                message: "Table Created Successfully!",
                type: "success",
            });
            closeModal();
            showTableModal(0, "ALL");
        } else {
            showToast({
                message: "Table Fail!",
                type: "error",
            });
        }
    } catch (error) {
        console.error(error);
        alert("Something went wrong.");
    }
}

/*
|--------------------------------------------------------------------------
| Date Filter Handler
|--------------------------------------------------------------------------
*/
function handleDateFilter() {
    const fromDate = document.getElementById("from_date").value;
    const toDate = document.getElementById("to_date").value;

    if (fromDate && toDate) {
        loadSales();
    }
}

/*
|--------------------------------------------------------------------------
| Fetch Data
|--------------------------------------------------------------------------
*/
document.getElementById("sale_data").addEventListener("click", () => {
    fetchSalesData(1); // start from page 1
    loadCategories();
    loadPaymentMethods();
});
// Example: filter inputs
const filters = [
    "from_date",
    "to_date",
    "invoice_paymentMethod",
    "customer_filter",
    "ProductSearchInput_sale_invoice",
    "category_filter",
    "sale_view_limit",
];

filters.forEach((id) => {
    const el = document.getElementById(id);
    if (!el) return;

    el.addEventListener("change", () => {
        // Only fetch if both dates are filled (if date filters)
        if (id === "from_date" || id === "to_date") {
            const from = document.getElementById("from_date").value;
            const to = document.getElementById("to_date").value;
            if (!from || !to) return;
        }
        fetchSalesData(1);
    });
});

function fetchSalesData(page = 1) {
    const params = new URLSearchParams();

    const from_date = document.getElementById("from_date").value;
    const to_date = document.getElementById("to_date").value;
    const invoice_paymentMethod = document.getElementById(
        "invoice_paymentMethod",
    ).value;
    const customer_filter = document.getElementById("customer_filter").value;
    const ProductSearchInput = document.getElementById(
        "ProductSearchInput_sale_invoice",
    ).value;
    const category_filter = document.getElementById("category_filter").value;
    const sale_view_limit = document.getElementById("sale_view_limit").value;

    if (from_date) params.append("from_date", from_date);
    if (to_date) params.append("to_date", to_date);
    if (invoice_paymentMethod)
        params.append("invoice_paymentMethod", invoice_paymentMethod);
    if (customer_filter) params.append("customer_filter", customer_filter);
    if (ProductSearchInput)
        params.append("ProductSearchInput", ProductSearchInput);
    if (category_filter) params.append("category_filter", category_filter);
    if (sale_view_limit) params.append("sale_view_limit", sale_view_limit);

    params.append("page", page);

    fetch(`/sales-report?${params.toString()}`)
        .then((res) => res.json())
        .then((data) => renderTable(data))
        .catch((err) => console.error(err));
}

/*
|--------------------------------------------------------------------------
| Debounce
|--------------------------------------------------------------------------
*/
function debounce(func, delay) {
    let timeout;
    return function () {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(), delay);
    };
}

let currentSortColumn = "h.invoice_date";
let currentSortDirection = "asc";
document.querySelectorAll("th[data-column]").forEach((th) => {
    th.addEventListener("click", function () {
        const column = this.getAttribute("data-column");

        // Toggle direction
        if (currentSortColumn === column) {
            currentSortDirection =
                currentSortDirection === "asc" ? "desc" : "asc";
        } else {
            currentSortColumn = column;
            currentSortDirection = "asc";
        }

        updateSortIcons();
        loadSales();
    });
});
function updateSortIcons() {
    document.querySelectorAll(".sort-icon").forEach((icon) => {
        icon.innerText = "↕";
    });

    const activeTh = document.querySelector(
        `th[data-column="${currentSortColumn}"] .sort-icon`,
    );

    if (activeTh) {
        activeTh.innerText = currentSortDirection === "asc" ? "↑" : "↓";
    }
}
// Helper functions
function formatMoney(value) {
    return (Number(value) || 0).toFixed(2); // always 2 decimals for money
}
function formatPercent(value) {
    return Math.round(Number(value) || 0); // round to integer for percent
}
function renderTable(response) {
    const tbody = document.getElementById("salesTableBody");
    const paginationContainer = document.getElementById(
        "paginationContainer_sale_invoice",
    );
    const pageInfo = document.getElementById("pageInfo_sale_invoice");

    // Clear previous content
    tbody.innerHTML = "";
    paginationContainer.innerHTML = "";
    pageInfo.innerHTML = "";

    if (!response.data || response.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="22" class="text-center py-4 text-gray-500">No data found</td></tr>`;
        return;
    }

    // Totals
    let subtotal = {
        quantity: 0,
        unit_price: 0,
        sell_price: 0,
        line_amount: 0,
        discount_percent: 0,
        discount_amount: 0,
        vat: 0,
        vat_amount: 0,
        total_amount: 0,
    };
    let rowCount = 0;
    let no = 1;

    const rows = []; // collect rows first to reduce DOM operations

    response.data.forEach((header) => {
        const lines = header.lines || [];
        if (!lines.length) return;

        lines.forEach((line) => {
            rowCount++;
            const cost_amount =
                (Number(line.cost) || 0) * (Number(line.quantity) || 0);

            rows.push(`
<tr class="text-nowrap">
    <td>${no++}</td>
    <td>${header.invoice_number ?? ""}</td>
    <td>${
        header.created_at
            ? new Date(header.created_at).toLocaleString("en-GB", {
                  day: "2-digit",
                  month: "short",
                  year: "numeric",
                  hour: "2-digit",
                  minute: "2-digit",
                  hour12: true,
              })
            : ""
    }</td>
      <td>${header.contact_name ?? ""}</td>
    <td>${header.phone ?? ""}</td>
    <td>${header.address ?? ""}</td>
    <td>${header.invoice_date ? new Date(header.invoice_date).toLocaleDateString("en-GB") : ""}</td>
    <td>${header.payment_method ?? ""}</td>
    <td>${header.customer_type ?? ""}</td>

    <td>${line.name ?? ""}</td>
    <td>${line.variant ?? ""}</td>
    <td>${line.description ?? ""}</td>
    <td class="text-right">${line.quantity ?? 0}</td>
    <td>${line.unit ?? ""}</td>

    <td class="text-right">${formatMoney(line.unit_price)} $</td>
    <td class="text-right">${formatMoney(line.sell_price)} $</td>

    <td class="text-right">${formatMoney(line.line_amount)} $</td>

    <td class="text-right">${formatPercent(line.discount_percent)} %</td>
    <td class="text-right">${formatMoney(line.discount_amount)} $</td>

    <td class="text-right">${formatPercent(line.vat)} %</td>
    <td class="text-right">${formatMoney(line.vat_amount)} $</td>

    <td class="text-right">${formatMoney(line.net_amount)} $</td>
    <td class="text-right">${formatMoney(line.grand_total_amount)} $</td>
</tr>
`);
            // accumulate totals
            subtotal.quantity += Number(line.quantity) || 0;
            subtotal.unit_price += Number(line.unit_price) || 0;
            subtotal.sell_price += Number(line.sell_price) || 0;
            subtotal.line_amount += Number(line.line_amount) || 0;
            subtotal.discount_percent += Number(line.discount_percent) || 0;
            subtotal.discount_amount += Number(line.discount_amount) || 0;
            subtotal.vat += Number(line.vat) || 0;
            subtotal.vat_amount += Number(line.vat_amount) || 0;
            subtotal.total_amount += Number(line.total_amount) || 0;
        });
    });

    // calculate averages
    subtotal.unit_price = subtotal.unit_price / rowCount;
    subtotal.sell_price = subtotal.sell_price / rowCount;
    subtotal.discount_percent = subtotal.discount_percent / rowCount;
    subtotal.vat = subtotal.vat / rowCount;

    // append all rows at once
    tbody.innerHTML = rows.join("");

    // append subtotal row
    body.innerHTML += `
    <tr class="bg-blue-200 font-semibold text-nowrap">
        <td colspan="10" class="text-left">Subtotal</td>
        <td class="text-right">${subtotal.quantity.toFixed(0)}</td>
        <td></td>
        <td class="text-right">AVG ${subtotal.unit_price.toFixed(2)} $</td>
        <td class="text-right">AVG ${subtotal.sell_price.toFixed(2)} $</td>
        <td class="text-right">${subtotal.line_amount.toFixed(2)} $</td>
        <td class="text-right">AVG ${Math.round(subtotal.discount_percent)} %</td>
        <td class="text-right">${subtotal.discount_amount.toFixed(2)} $</td>
        <td class="text-right">AVG ${Math.round(subtotal.vat)} %</td>
        <td class="text-right">${subtotal.vat_amount.toFixed(2)} $</td>
        <td class="text-right">${subtotal.net_amount.toFixed(2)} $</td>
        <td class="text-right">${subtotal.grand_total_amount.toFixed(2)} $</td>
    </tr>
    `;

    // ----------------------
    // PAGINATION
    // ----------------------
    const totalPages = response.last_page || 1;
    const currentPage = response.current_page || 1;

    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;

    // Prev button
    if (currentPage > 1) {
        const prevBtn = document.createElement("button");
        prevBtn.textContent = "Prev";
        prevBtn.className = "px-3 py-1 border rounded bg-white";
        prevBtn.onclick = () => fetchSalesData(currentPage - 1);
        paginationContainer.appendChild(prevBtn);
    }

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement("button");
        btn.textContent = i;
        btn.className = `px-3 py-1 border rounded text-sm ${i === currentPage ? "bg-blue-600 text-white" : "bg-white text-gray-700 hover:bg-gray-100"}`;
        btn.onclick = () => fetchSalesData(i);
        paginationContainer.appendChild(btn);
    }

    // Next button
    if (currentPage < totalPages) {
        const nextBtn = document.createElement("button");
        nextBtn.textContent = "Next";
        nextBtn.className = "px-3 py-1 border rounded bg-white";
        nextBtn.onclick = () => fetchSalesData(currentPage + 1);
        paginationContainer.appendChild(nextBtn);
    }
}

async function loadCategories() {
    try {
        const res = await fetch("/sales/categories");
        const categories = await res.json();

        const select = document.getElementById("category_filter");
        select.innerHTML = '<option value="">All Categories</option>'; // Clear existing options
        categories.forEach((category) => {
            const option = document.createElement("option");
            option.value = category;
            option.textContent = category;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading categories:", error);
    }
}

async function loadPaymentMethods() {
    try {
        const res = await fetch("/sales/payment-methods");
        const methods = await res.json();

        const select = document.getElementById("invoice_paymentMethod");
        select.innerHTML = '<option value="">All Payment</option>'; // Clear existing options
        methods.forEach((method) => {
            const option = document.createElement("option");
            option.value = method; // value for filtering
            option.textContent = method; // text shown to user
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading payment methods:", error);
    }
}
const customerInput = document.getElementById("customer_search");
const customerList = document.getElementById("customer_list");
const customerHidden = document.getElementById("customer_filter");

let debounceTimer;

customerInput.addEventListener("input", function () {
    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        fetchCustomers(this.value);
    }, 300); // debounce 300ms
});

async function fetchCustomers(search = "") {
    try {
        const res = await fetch(`/sales/customer-search?search=${search}`);
        const customers = await res.json();

        customerList.innerHTML = "";

        if (customers.length === 0) {
            customerList.classList.add("hidden");
            return;
        }

        customers.forEach((customer) => {
            const li = document.createElement("li");
            li.textContent = customer.name;
            li.className = "px-3 py-2 hover:bg-gray-100 cursor-pointer";

            li.addEventListener("click", () => {
                customerInput.value = customer.name;
                customerHidden.value = customer.id;
                customerList.classList.add("hidden");

                fetchSalesData(1); // auto filter
            });

            customerList.appendChild(li);
        });

        customerList.classList.remove("hidden");
    } catch (error) {
        console.error("Customer search error:", error);
    }
}

// Hide dropdown when clicking outside
document.addEventListener("click", function (e) {
    if (!customerInput.contains(e.target) && !customerList.contains(e.target)) {
        customerList.classList.add("hidden");
    }
});

const productInput = document.getElementById("product_search");
const productDatalist = document.getElementById("product_datalist");
const productHidden = document.getElementById(
    "ProductSearchInput_sale_invoice",
);

let productDebounce;

productInput.addEventListener("input", function () {
    clearTimeout(productDebounce);

    productDebounce = setTimeout(() => {
        fetchProducts(this.value);
    }, 300);
});

async function fetchProducts(search = "") {
    try {
        const res = await fetch(`/sales/product-search?search=${search}`);
        const products = await res.json();

        productDatalist.innerHTML = "";
        console.log(products);
        products.forEach((product) => {
            const option = document.createElement("option");
            option.value = product; // what user sees
            option.dataset.code = product;
            productDatalist.appendChild(option);
        });
    } catch (error) {
        console.error("Product search error:", error);
    }
}
function exportTableToExcelXLSX(tableId, filename = "sales.xlsx") {
    const table = document.getElementById(tableId);
    const rows = Array.from(table.querySelectorAll("tr"));

    let data = rows
        .filter((row) => !row.classList.contains("bg-blue-200")) // Skip subtotal row
        .map((row) => {
            return Array.from(row.querySelectorAll("td, th")).map((cell) => {
                let text = cell.textContent.replace(/↕/g, "").trim();
                let value = cell.dataset.value ?? text;

                if (typeof value === "string") {
                    let cleaned = value.replace(/,/g, "").replace(/[$%]/g, "");
                    if (cleaned !== "" && !isNaN(cleaned)) {
                        return Number(cleaned);
                    }
                }

                return value;
            });
        });

    // 🔥 SORT BY INVOICE NO (2nd column)
    const sortColumnIndex = 1;

    const header = data[0];
    const body = data.slice(1);

    function extractInvoiceNumber(value) {
        const match = String(value).match(/-(\d+)$/);
        return match ? parseInt(match[1], 10) : 0;
    }

    body.sort((a, b) => {
        const numA = extractInvoiceNumber(a[sortColumnIndex]);
        const numB = extractInvoiceNumber(b[sortColumnIndex]);

        // return numB - numA; // 🔥 DESC (latest first)
        return numA - numB; // ✅ ASC
    });

    data = [header, ...body];

    // Create workbook and worksheet
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(data);

    // Align all cells left
    const range = XLSX.utils.decode_range(ws["!ref"]);
    for (let R = range.s.r; R <= range.e.r; ++R) {
        for (let C = range.s.c; C <= range.e.c; ++C) {
            const cell_address = { c: C, r: R };
            const cell_ref = XLSX.utils.encode_cell(cell_address);
            if (!ws[cell_ref]) continue;

            ws[cell_ref].s = {
                alignment: { horizontal: "left" },
            };
        }
    }

    XLSX.utils.book_append_sheet(wb, ws, "Sales Report");
    XLSX.writeFile(wb, filename);
}
// Hook download button
document.getElementById("downloadSales").addEventListener("click", () => {
    exportTableToExcelXLSX("Table-sale-list");
});

document
    .getElementById("btnPrintProduct")
    .addEventListener("click", function () {
        let table = document.querySelector("#product-list table");

        if (!table) {
            alert("No product data to print.");
            return;
        }

        let printWindow = window.open("", "", "width=1200,height=800");

        printWindow.document.write(`
        <html>
        <head>
            <title>Product List</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                }

                h2 {
                    text-align: center;
                    margin-bottom: 20px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 12px;
                }

                th, td {
                    border: 1px solid #000;
                    padding: 6px;
                    text-align: left;
                    text-wrap: nowrap;
                }

                th {
                    background: #f2f2f2;
                }
                   th:first-child, td:first-child {
                    display: none; /* Hide the first column (ID) */
                }
                img {
                    max-height: 40px;
                }

                @media print {
                    button { display: none; }
                }
            </style>
        </head>
        <body>
            <h2>Product List</h2>
            ${table.outerHTML}
        </body>
        </html>
    `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    });

document
    .getElementById("btnPrintCustomer")
    .addEventListener("click", function () {
        let table = document.querySelector("#customer-list");

        if (!table) {
            alert("No customer data to print.");
            return;
        }

        let printWindow = window.open("", "", "width=1200,height=800");

        let now = new Date();
        let formattedDate = now.toLocaleString();

        printWindow.document.write(`
        <html>
        <head>
            <title>Customer List</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                }

                h2 {
                    text-align: center;
                    margin-bottom: 5px;
                }

                .print-date {
                    text-align: right;
                    font-size: 12px;
                    margin-bottom: 15px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 12px;
                }

                th, td {
                    border: 1px solid #000;
                    padding: 6px;
                    text-align: left;
                }

                th {
                    background: #f2f2f2;
                }

                @media print {
                    button { display: none; }
                }
            </style>
        </head>
        <body>
            <h2>Customer List</h2>
            <div class="print-date">Printed: ${formattedDate}</div>
            ${table.outerHTML}
        </body>
        </html>
    `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    });
document.getElementById("btnPrintSale").addEventListener("click", function () {
    let table = document.getElementById("Table-sale-list");

    if (!table) {
        alert("No sale data to print.");
        return;
    }

    let printWindow = window.open("", "", "width=1400,height=900");
    let from_date = document.getElementById("from_date").value;
    let to_date = document.getElementById("to_date").value;
    let dateRangeText = "";

    if (from_date && to_date) {
        const formatDate = (dateStr) => {
            const date = new Date(dateStr);
            return date.toLocaleDateString("en-GB", {
                day: "2-digit",
                month: "long",
                year: "numeric",
            });
        };

        if (from_date === to_date) {
            dateRangeText = `Date: ${formatDate(from_date)}`;
        } else {
            const from = formatDate(from_date);
            const to = formatDate(to_date);
            dateRangeText = `From ${from} To ${to}`;
        }
    }
    let now = new Date();

    let subtotalCell = document.getElementById("subtotal");
    if (subtotalCell) {
        subtotalCell.setAttribute("colspan", "4");
    }
    let today = new Date().toLocaleDateString();

    let formatted = now.toLocaleString("en-GB", {
        day: "2-digit",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
    printWindow.document.write(`
        <html>
        <head>
            <title>Sale Report</title>
            <style>
                @page {
                    size: A4 landscape;
                    margin: 8mm;
                    margin-bottom: 0mm; /* extra space for footer */
                }

                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 10px;
                    color: #333;
                       padding-bottom: 60px; /* leave space for footer */
                         font-family: 'Noto Serif Khmer', serif;
                }

                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 2px solid #000;
                    padding-bottom: 10px;
                    margin-bottom: 10px;
                }

                .company {
                    font-size: 18px;
                    font-weight: bold;
                }

                .report-title {
                    text-align: center;
                    font-size: 20px;
                    font-weight: bold;
                    margin: 10px 0;
                }

                .print-date {
                    text-align: right;
                    font-size: 12px;
                    margin-bottom: 10px;
                }

                table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5px;
    table-layout: fixed; /* ✅ important for fitting */
}

thead {
    display: table-header-group;
    background-color: #f2f2f2;
}

tfoot {
    display: table-footer-group;
}

table th, table td {
    border: 1px solid #ccc;
    padding: 3px 5px;       /* ✅ smaller padding */
    font-size: 10px;        /* ✅ smaller text */
    line-height: 1.2;       /* ✅ tighter rows */
    word-wrap: break-word;  /* ✅ wrap long text */
}

table th {
    text-align: center;
    font-weight: bold;
    font-size: 10px;
}

tr {
    page-break-inside: avoid;
}

                table th:nth-child(1), table td:nth-child(1) {
                                width:20px;
                        }
                    table th:nth-child(3), table td:nth-child(3) {
                        display: none;
                }
                           table th:nth-child(4), table td:nth-child(4) {
                        display: none;
                }

                            table th:nth-child(6), table td:nth-child(6) {
                        display: none;
                }
                                  table th:nth-child(7), table td:nth-child(7) {
                        display: none;
                }
                                    table th:nth-child(8), table td:nth-child(8) {
                                    width: 40%;
                            text-align: left;
                            white-space: nowrap;
                            }
                            table th:nth-child(9), table td:nth-child(9) {
                        display: none;
                }
                        table th:nth-child(10), table td:nth-child(10) {
                        display: none;
                }
                                table th:nth-child(11), table td:nth-child(11) ,       table th:nth-child(14), table td:nth-child(14),       table th:nth-child(15), table td:nth-child(15),       table th:nth-child(16), table td:nth-child(16),       table th:nth-child(20), table td:nth-child(20){
                          text-align: right;
                }
                            table th:nth-child(13), table td:nth-child(13) {
                        display: none;
                }

                                table th:nth-child(17), table td:nth-child(17) {
                        display: none;
                }
                                    table th:nth-child(18), table td:nth-child(18) {
                        display: none;
                }
                                           table th:nth-child(19), table td:nth-child(19) {
                        display: none;
                }
                .footer {
                    margin-top: 20px;
                    display: flex;
                    justify-content: space-between;
                    font-size: 12px;
                }
                    #subtotal-row{
                    display: none;}
                #avg-unit-price{
                display: none;
                }
                #avg-sell-price{
                display: none;
                }
                #avg-line-amount{
                display: none;
                }
                #avg-discount-percent{
                display: none;
                }
                #discount-amount{
                display: none;
                }
                #avg-vat{
                display: none;
                }
                #vat-amount{
                display: none;
                }
                .sort-icon{
                display: none;}
                .footer {
                    position: fixed;
                    bottom: 10mm;
                    left: 0;
                    width: 100%;
                    display: flex;
                    justify-content: space-between;
                    padding: 0 40px;
                    font-size: 10px;
                }



                @media print {
                    body {
                        margin: 5mm;
                    }
                }
            </style>
        </head>

        <body>

            <!-- HEADER -->
            <div class="header">
                <div class="company">Confirel Co., Ltd.</div>
                <div>Sale Report</div>
            </div>

            <div class="report-title">SALE REPORT ${dateRangeText}</div>

            <div class="print-date">
                Printed: ${formatted}
            </div>

            <!-- TABLE -->
            ${table.outerHTML}

            <!-- FOOTER (STAYS TOGETHER) -->
            <div class="no-break">
               <div class="footer">
    <div>
        Prepared by:<br>
        <div style="margin-top: 20px;">______________________</div><br>
        Date: ${today}
    </div>

    <div>
        Approved by:<br>
        <div style="margin-top: 20px;">_____________________________________</div><br>
        Date: ____________
    </div>
</div>

            </div>

        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();

    // wait for render before print
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 700);
    subtotalCell.setAttribute("colspan", "10");
});

document.getElementById("btnReciept").addEventListener("click", function () {
    let invoiceNo = prompt("Enter Invoice Number:");

    if (invoiceNo !== null && invoiceNo.trim() !== "") {
        Livewire.dispatch("printReciept", { invoice_no: invoiceNo });
    } else {
        alert("Invoice number is required!");
    }
});

document.getElementById("btnPrintProductMenu").addEventListener("click", () => {
    if (!allProducts || !allProducts.length) {
        alert("No products loaded to print!");
        return;
    }

    // Group products by category
    const categories = {};
    allProducts.forEach((product) => {
        const category = (product.category && product.category.name) || "Other";
        if (!categories[category]) categories[category] = [];
        categories[category].push(product);
    });

    // Build HTML: categories with underline
    let html = "";

    Object.keys(categories).forEach((cat) => {
        html += `<div class="menu-category-block">
                    <div class="menu-category-title" contenteditable="true">${cat}</div>`;

        categories[cat].forEach((item) => {
            const price = parseFloat(item.sell_price || item.price) || 0;
            const discount = parseFloat(item.discount_percent || 0);
            const imgSrc = item.image
                ? `/assets/startic_img/${encodeURIComponent(item.image)}`
                : "";

            html += `
                <div class="menu-card">
                    ${
                        imgSrc
                            ? `<img src="${imgSrc}" class="menu-img">`
                            : `<div class="menu-img placeholder">No Image</div>`
                    }
                    <div class="menu-details">
                        <div class="menu-name" contenteditable="true">${item.name}</div>
                        ${
                            discount > 0
                                ? `<div class="menu-price" contenteditable="true">
                                    <del>$${price.toFixed(2)}</del> → $${(price * (1 - discount / 100)).toFixed(2)} (${discount}% Off)
                               </div>`
                                : `<div class="menu-price" contenteditable="true">$${price.toFixed(2)}</div>`
                        }
                    </div>
                </div>
            `;
        });

        html += `</div>`; // close category block
    });

    openEditableMenuPreview(html);
});

function openEditableMenuPreview(content) {
    const win = window.open(
        "",
        "",
        "width=1200,height=800,scrollbars=yes,resizable=yes",
    );
    win.document.write(`
        <html>
        <head>
            <title>Editable Product Menu</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&family=Playfair+Display:wght@700&display=swap');
                body { font-family: 'Montserrat', sans-serif; padding:20px; background:#fff; color:#333; }

                /* Toolbar fixed at bottom */
                .toolbar {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    width: 100%;
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    background: #f8f8f8;
                    border-top: 1px solid #ccc;
                    padding: 10px;
                    z-index: 999;
                }
                .toolbar label { font-size:12px; display:flex; flex-direction:column; }
                .toolbar input, .toolbar select, .toolbar button { padding:4px 6px; margin:2px 0; cursor:pointer; }

                /* Category */
                .menu-category-block { margin-bottom:30px; width:100%; }
                .menu-category-title {
                    font-size: 18px;
                    font-weight: 700;
                    margin-bottom: 10px;
                    text-align: center;
                    border-bottom: 2px solid #000;
                    padding-bottom: 3px;
                    width:100%;
                }

                /* Products grid */
                .menu-products {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr); /* default 4 columns */
                    gap: 15px;
                    width:100%;
                }

                /* Cards */
                .menu-card {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    padding: 5px;
                    background:#fff;
                    page-break-inside: avoid;
                    box-shadow:0 1px 3px rgba(0,0,0,0.1);
                    transition: transform 0.2s;
                }
                .menu-card:hover { transform: translateY(-2px); }

                .menu-img { width:100%; height:150px; object-fit:cover; border-radius:4px; margin-bottom:5px; }
                .menu-img.placeholder {
                    background:#f0f0f0; display:flex; align-items:center; justify-content:center; color:#999; height:150px; width:100%; border-radius:4px; margin-bottom:5px;
                }

                .menu-details { text-align:center; width:100%; }
                .menu-name { font-weight:700; font-size:14px; text-transform:capitalize; }
                .menu-price { font-size:13px; color:#b33; margin-top:3px; font-weight:600; }
                .menu-price del { color:#888; font-weight:400; margin-right:4px; }

                [contenteditable="true"] { outline:none; padding:2px; }

                @media print { .toolbar { display:none; } body { -webkit-print-color-adjust: exact; } }
            </style>
        </head>
        <body>
            <div id="menuContainer">${content}</div>

            <div class="toolbar">
                <button id="printBtn">Print</button>
                <label>Columns
                    <input type="number" id="colInput" value="4" min="2" max="6" style="width:50px;">
                </label>
                <label>Card Height
                    <input type="number" id="cardHeightInput" value="150" min="50" max="400" style="width:50px;">px
                </label>
                <label><input type="checkbox" id="toggleImage" checked> Show Images</label>
                <label>Text Align
                    <select id="textAlignSelect">
                        <option value="left">Left</option>
                        <option value="center" selected>Center</option>
                        <option value="right">Right</option>
                    </select>
                </label>
                <label>Text Color
                    <input type="color" id="textColorPicker" value="#333">
                </label>
                <label>Font Size
                    <input type="number" id="fontSizeInput" value="14" min="8" max="30">
                </label>
            </div>

            <script>
                const menuContainer = document.getElementById('menuContainer');

                // Print
                document.getElementById('printBtn').onclick = () => { window.print(); };

                // Columns
                document.getElementById('colInput').oninput = (e) => {
                    const cols = parseInt(e.target.value) || 4;
                    menuContainer.querySelectorAll('.menu-products').forEach(grid => {
                        grid.style.gridTemplateColumns = 'repeat(' + cols + ', 1fr)';
                    });
                };

                // Card Height
                document.getElementById('cardHeightInput').oninput = (e) => {
                    const h = parseInt(e.target.value) || 150;
                    menuContainer.querySelectorAll('.menu-img, .menu-img.placeholder').forEach(img => {
                        img.style.height = h + 'px';
                    });
                };

                // Show / hide images
                document.getElementById('toggleImage').onchange = (e) => {
                    const show = e.target.checked;
                    menuContainer.querySelectorAll('.menu-img, .menu-img.placeholder').forEach(img => {
                        img.style.display = show ? 'block' : 'none';
                    });
                };

                // Text Align
                document.getElementById('textAlignSelect').onchange = (e) => {
                    menuContainer.querySelectorAll('[contenteditable="true"]').forEach(el => {
                        el.style.textAlign = e.target.value;
                    });
                };

                // Text Color
                document.getElementById('textColorPicker').oninput = (e) => {
                    menuContainer.querySelectorAll('[contenteditable="true"]').forEach(el => {
                        el.style.color = e.target.value;
                    });
                };

                // Font Size
                document.getElementById('fontSizeInput').oninput = (e) => {
                    const size = parseInt(e.target.value) || 14;
                    menuContainer.querySelectorAll('[contenteditable="true"]').forEach(el => {
                        el.style.fontSize = size + 'px';
                    });
                };
            </script>
        </body>
        </html>
    `);
}

// Filters
// Only bind click if admin and button exists
if (user_role === "admin") {
    const puchasing_btn = document.getElementById("purchasing");
    // Confirm purchasing
    puchasing_btn.addEventListener("click", () => {
        window.location.href = "/Purchasing";
    });

    let userBtn = document.getElementById("user_data");
    userBtn.addEventListener("click", fetchUsers);
    // document.getElementById("user_data").addEventListener("click", fetchUsers);
    document
        .getElementById("userSearchInput")
        .addEventListener("input", fetchUsers);
    document
        .getElementById("role_filter")
        .addEventListener("change", fetchUsers);
    document.getElementById("active").addEventListener("change", fetchUsers);
    // Trigger fetch when modal opens
    document
        .getElementById("default-modal-user-list")
        .addEventListener("transitionend", function () {
            if (!this.classList.contains("hidden")) {
                fetchUsers();
            }
        });

    document
        .getElementById("btnUser")
        .addEventListener("click", loadWarehouses_user);

    async function fetchUsers() {
        const search = document.getElementById("userSearchInput").value.trim();
        const role = document.getElementById("role_filter").value;
        const active = document.getElementById("active").value;

        const params = new URLSearchParams({ search, role, active });

        try {
            const res = await fetch(`/users-list-data?${params.toString()}`);
            const users = await res.json();
            renderUsers(users);
        } catch (err) {
            console.error(err);
        }
    }
    function renderUsers(users) {
        const tbody = document.getElementById("user-table-body");
        tbody.innerHTML = "";

        if (!users.length) {
            tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-gray-500">No users found</td></tr>`;
            return;
        }

        users.forEach((user) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
            <td class="px-4 py-2 text-center">
                <input type="radio" name="selectUser" value="${user.id}">
            </td>
            <td class="px-4 py-2">${user.id}</td>
            <td class="px-4 py-2">${user.name}</td>
            <td class="px-4 py-2">${user.email}</td>
            <td class="px-4 py-2">${user.phone || "-"}</td>
            <td class="px-4 py-2">${user.role}</td>

             <td class="px-3      text-sm ${user.status ? "text-green-600" : "text-red-500"}">
                    ${user.status ? "Active" : "Inactive"}
                </td>
        `;

            tbody.appendChild(tr);
        });
    }

    const displayName = document.getElementById("display_name");
    const username = document.getElementById("username");
    const role = document.getElementById("role");
    const email = document.getElementById("email");
    const submitBtn = document.getElementById("submitBtn");

    const password = document.getElementById("password");
    const formError = document.getElementById("formError");

    // warehouse checkbox change (IMPORTANT)
    displayName.addEventListener("input", validateForm);
    username.addEventListener("input", validateForm);
    role.addEventListener("change", validateForm);
    email.addEventListener("input", validateForm);

    function validateForm() {
        let errors = [];

        const nameValid = displayName.value.trim() !== "";
        const userValid = username.value.trim() !== "";
        const roleValid = role.value !== "";

        const emailValue = email.value.trim();
        const isEmailFormatValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
            emailValue,
        );

        const passwordValid = password.value.trim() !== "";

        const warehouseChecked =
            document.querySelectorAll('input[name="warehouses[]"]:checked')
                .length > 0;

        // 🔴 Collect errors
        if (!nameValid) errors.push("Display name is required");
        if (!userValid) errors.push("Username is required");
        if (!roleValid) errors.push("Role is required");
        if (!passwordValid) errors.push("Password is required");
        if (!warehouseChecked) errors.push("Select at least 1 warehouse");
        if (emailValue !== "" && !isEmailFormatValid) {
            errors.push("Email format is invalid");
        }

        // 🔥 Show ALL messages in ONE place
        if (errors.length > 0) {
            formError.classList.remove("hidden");
            formError.innerHTML = errors.join("<br>");
        } else {
            formError.classList.add("hidden");
            formError.innerHTML = "";
        }

        // ✅ Enable / disable button
        if (errors.length === 0) {
            submitBtn.disabled = false;
            submitBtn.classList.remove("bg-gray-400", "cursor-not-allowed");
            submitBtn.classList.add("bg-green-500", "text-white");
            submitBtn.textContent = "Create User";
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add("bg-gray-400", "cursor-not-allowed");
            submitBtn.textContent = "Required More Info";
        }
    }

    document.addEventListener("change", function (e) {
        if (e.target.name === "warehouses[]") {
            validateForm();
        }
    });

    async function loadWarehouses_user() {
        try {
            const res = await fetch("/warehouse-list-data");
            const data = await res.json();

            const container = document.getElementById("warehouseList");
            container.innerHTML = "";

            if (!data.length) {
                container.innerHTML =
                    "<p class='text-gray-500'>No warehouse found</p>";
                return;
            }

            data.forEach((w) => {
                const div = document.createElement("label");
                div.className = `
        flex items-center gap-3 p-3 rounded-lg border border-gray-300
        cursor-pointer transition-all duration-150
        hover:border-amber-500 hover:bg-amber-50
    `;

                div.innerHTML = `
        <input type="checkbox" name="warehouses[]" value="${w.id}"
            class="w-4 h-4 accent-amber-500">

        <span class="text-sm font-medium text-gray-700">
            ${w.name}
        </span>
    `;

                // active style when checked
                const checkbox = div.querySelector("input");
                checkbox.addEventListener("change", () => {
                    if (checkbox.checked) {
                        div.classList.add("border-amber-500", "bg-amber-100");
                    } else {
                        div.classList.remove(
                            "border-amber-500",
                            "bg-amber-100",
                        );
                    }
                });

                container.appendChild(div);
            });
        } catch (err) {
            console.error("Error loading warehouses:", err);
        }
    }

    document
        .getElementById("AddUserForm")
        .addEventListener("submit", async function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            try {
                const res = await fetch("/users/store", {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'input[name="_token"]',
                        ).value,
                    },
                });

                const data = await res.json();

                if (data.success) {
                    showToast({
                        message: data.message || "User created successfully ✅",
                        type: "success",
                    });
                    form.reset();
                } else {
                    showToast({
                        message: data.message || "Failed to create user ❌",
                        type: "error",
                    });
                }
            } catch (err) {
                console.error(err);

                showToast({
                    message: "Error",
                    type: "error",
                });
            }
        });
}

// document.addEventListener("DOMContentLoaded", function () {
//     const welcome = document.getElementById("welcomeScreen");
//     const main = document.getElementById("mainContent");

//     // Only show first time
//     if (!sessionStorage.getItem("welcomeShown")) {
//         sessionStorage.setItem("welcomeShown", "true");

//         setTimeout(() => {
//             welcome.style.display = "none";
//             main.style.opacity = 1; // show main content
//         }, 5000); // 5 seconds animation
//     } else {
//         // skip welcome if already shown
//         welcome.style.display = "none";
//         main.style.opacity = 1;
//     }
// });
const logoutBtn = document.getElementById("logout");
const modal_logout = document.getElementById("logoutModal");
const confirmBtn_logout = document.getElementById("confirmLogout");
const cancelBtn_logout = document.getElementById("cancelLogout");

// Show modal
logoutBtn.addEventListener("click", () => {
    modal_logout.classList.remove("hidden");
    document.body.style.overflow = "hidden"; // prevent scroll
});

// Confirm logout
confirmBtn_logout.addEventListener("click", () => {
    window.location.href = "/logout";
});

// Cancel logout
cancelBtn_logout.addEventListener("click", () => {
    modal_logout.classList.add("hidden");
    document.body.style.overflow = ""; // restore scroll
});

// Optional: click outside modal to close
modal_logout.addEventListener("click", (e) => {
    if (e.target === modal_logout) {
        modal_logout.classList.add("hidden");
        document.body.style.overflow = "";
    }
});
let currentCartIndex = null;
let currentProductId = null;
let currentTrackQty = 0;

function openLotModal(cart_index, product_id, product_name) {
    currentCartIndex = cart_index;
    currentProductId = product_id;
    const qtyInput = document.querySelector(`#qty_order_${cart_index}`);

    let maxQty = parseInt(qtyInput.max);
    // Current value (number)
    currentTrackQty = parseInt(qtyInput.value) || 0;

    if (currentTrackQty > maxQty) {
        showToast({
            message: `Quantity adjusted to max available (${maxQty})`,
            type: "error",
        });

        currentTrackQty = maxQty;
    }
    document.getElementById("item-id").textContent =
        `Product: ${product_name} (ID: ${product_id}) | Track Qty: ${currentTrackQty}`;

    // Grab original product image from main page
    let img = document.getElementById("product-image" + product_id);
    let display_img = document.getElementById("display_img");

    if (img) {
        display_img.src = img.src; // copy image URL
    } else {
        display_img.src = "https://via.placeholder.com/150"; // fallback
    }

    document.getElementById("lotModal").classList.remove("hidden");

    loadLotData(product_id);
}

async function loadLotData(product_id) {
    try {
        const modalBody = document.getElementById("lotModalBody");
        modalBody.innerHTML = "<p class='text-gray-500'>Loading lots...</p>";

        const res = await fetch(`/get-lot-data/${product_id}`);
        const data = await res.json();

        if (!data.length) {
            modalBody.innerHTML =
                "<p class='text-red-500'>No stock available</p>";
            return;
        }

        modalBody.innerHTML = ""; // clear previous

        // Table header
        const header = document.createElement("div");
        header.className =
            "grid grid-cols-5 gap-2 font-semibold border-b pb-1 mb-2 text-gray-700";
        header.innerHTML = `
            <div>Lot No</div>
            <div>Qty to pick</div>
            <div>Stock</div>
            <div>Expire</div>
            <div>Warehouse </div>

        `;
        modalBody.appendChild(header);

        // Table rows
        data.forEach((lot) => {
            const row = document.createElement("div");
            row.className =
                "grid grid-cols-5 gap-2 items-center p-1 bg-white rounded shadow-sm";

            // Expired check
            let expireClass = "";
            if (lot.expire && new Date(lot.expire) < new Date()) {
                expireClass = "text-red-500 font-bold";
            }

            // Low stock color
            let stockClass = "";
            if (lot.qty <= 5) stockClass = "text-yellow-600 font-semibold";
            let formattedExpire = "-";
            if (lot.expire) {
                const d = new Date(lot.expire);
                const months = [
                    "Jan",
                    "Feb",
                    "Mar",
                    "Apr",
                    "May",
                    "Jun",
                    "Jul",
                    "Aug",
                    "Sep",
                    "Oct",
                    "Nov",
                    "Dec",
                ];
                const day = String(d.getDate()).padStart(2, "0");
                const month = months[d.getMonth()];
                const year = d.getFullYear();
                formattedExpire = `${day}-${month}-${year}`;
            }
            row.innerHTML = `
        <!-- Hidden Warehouse Product ID -->
        <input type="hidden" class="lot-id" value="${lot.id}">

        <input type="text" value="${lot.lot ?? "NO LOT"}" readonly
            class="border px-2 py-1 rounded bg-gray-100 w-full">

        <input type="number" min="0" max="${lot.qty}" value="0"
            class="border px-2 py-1 rounded lot-qty w-full"
            oninput="updateLotWarning()">

        <span class="${stockClass} text-center">${lot.qty}</span>

        <span class="${expireClass} text-center">${formattedExpire}</span>
             <span class="text-center text-nowrap">${lot.warehouse_name}</span>
    `;

            modalBody.appendChild(row);
        });
    } catch (err) {
        console.error("Error loading lot data:", err);
        modalBody.innerHTML =
            "<p class='text-red-500'>Failed to load lots.</p>";
    }
}
function saveLots() {
    const rows = document.querySelectorAll("#lotModalBody .lot-id");
    let lots = [];
    let total = 0;

    rows.forEach((lotInput) => {
        const row = lotInput.closest("div"); // get parent row
        const lotId = lotInput.value;
        const qty = parseInt(row.querySelector(".lot-qty").value || 0);

        if (qty > 0) {
            lots.push({ id: lotId, qty });
            total += qty;
        }
    });

    const warning = document.getElementById("lot-warning");
    const saveBtn = document.getElementById("save-lot-btn");

    if (total !== currentTrackQty) {
        warning.textContent = `Total lot qty (${total}) must equal item qty (${currentTrackQty})`;
        warning.classList.remove("hidden");

        saveBtn.disabled = true;
        saveBtn.classList.remove("bg-emerald-500", "hover:bg-emerald-600");
        saveBtn.classList.add("bg-gray-400", "cursor-not-allowed");
        return;
    }

    console.log("Selected lots:", lots);
    // ✅ Send to Livewire/cart with warehouse_product IDs

    Livewire.dispatch("set-item-lots", {
        index: currentCartIndex,
        lots: lots,
    });

    // Clear warning and close modal
    warning.classList.add("hidden");
    closeLotModal();

    // Optional: show small toast confirmation
    showToast({ message: "Lots saved successfully!", type: "success" });
}

// Close function
function closeViewLotModal() {
    document.getElementById("viewLotModal").classList.add("hidden");
}

function updateLotWarning() {
    const inputs = document.querySelectorAll("#lotModalBody .lot-qty");
    let total = 0;

    inputs.forEach((input) => {
        let val = parseInt(input.value || 0);

        // clamp each lot to 0..max allowed for that lot
        const maxLot = parseInt(input.max || 0);
        if (val > maxLot) {
            val = maxLot;
            input.value = val;
        }

        total += val;
    });

    const warning = document.getElementById("lot-warning");
    const saveBtn = document.getElementById("save-lot-btn");

    if (total !== currentTrackQty) {
        warning.textContent = `Total lot qty (${total}) must equal item qty (${currentTrackQty})!`;
        warning.classList.remove("hidden");

        saveBtn.disabled = true;
        saveBtn.classList.remove("bg-emerald-500", "hover:bg-emerald-600");
        saveBtn.classList.add("bg-gray-400", "cursor-not-allowed");
    } else {
        warning.classList.add("hidden");

        saveBtn.disabled = false;
        saveBtn.classList.remove("bg-gray-400", "cursor-not-allowed");
        saveBtn.classList.add("bg-emerald-500", "hover:bg-emerald-600");
    }
}
function closeLotModal() {
    document.getElementById("lotModal").classList.add("hidden");
}

function openLotModal_transfer(row_id, lot_id) {
    document.getElementById("transfer_modal").classList.remove("hidden");
    console.log("row: " + row_id);
    wh_product_id = lot_id;
    const row = document.getElementById(`transfer_row-${row_id}`);
    const cells = row.querySelectorAll("td");

    document.getElementById("location-display").textContent =
        cells[14].textContent;

    document.getElementById("from_location_body").innerHTML = `
      <tr>
        <td class="text-left">${cells[2].textContent}</td>
        <td class="text-left">${cells[5].textContent}</td>
        <td class="text-right">${cells[7].textContent}</td>
        <td class="text-left">${cells[8].textContent}</td>
      </tr>
    `;

    const select = document.getElementById("to_location_select");
    const qtyInput = document.getElementById("transfer_qty");

    const currentWh = cells[14].textContent.trim();

    select.innerHTML = `<option value="">Select warehouse</option>`;

    wh.forEach((item) => {
        if (item.name.trim() !== currentWh) {
            let option = document.createElement("option");
            option.value = item.id;
            option.textContent = item.name;

            select.appendChild(option);
        }
    });

    const availableQty = Number(cells[7].textContent) || 0;

    qtyInput.min = 1;
    qtyInput.max = availableQty;
    qtyInput.value = "";

    validateTransferForm();
}

function closeLotModal_transfer() {
    document.getElementById("transfer_modal").classList.add("hidden");
}

function validateTransferForm() {
    const btn = document.getElementById("confirmTransferBtn");
    const qtyInput = document.getElementById("transfer_qty");
    const warehouseSelect = document.getElementById("to_location_select");
    const qtyError = document.getElementById("transfer_qty_error");

    if (!btn || !qtyInput || !warehouseSelect) return;

    const min = Number(qtyInput.min) || 1;
    const max = Number(qtyInput.max) || 0;
    const qtyValue = qtyInput.value.trim();

    let valid = false;

    if (qtyValue === "") {
        if (qtyError) {
            qtyError.textContent = "Please enter quantity";
            qtyError.classList.remove("hidden");
        }
    } else {
        const qty = Number(qtyValue);

        if (isNaN(qty) || qty < min || qty > max) {
            if (qtyError) {
                qtyError.textContent = `Qty must be between ${min} and ${max}`;
                qtyError.classList.remove("hidden");
            }
        } else {
            if (qtyError) {
                qtyError.textContent = "";
                qtyError.classList.add("hidden");
            }

            if (warehouseSelect.value !== "") {
                valid = true;
            }
        }
    }

    if (valid) {
        btn.disabled = false;
        btn.classList.remove("bg-gray-400", "cursor-not-allowed");
        btn.classList.add(
            "bg-green-500",
            "hover:bg-green-600",
            "cursor-pointer",
        );
    } else {
        btn.disabled = true;
        btn.classList.remove(
            "bg-green-500",
            "hover:bg-green-600",
            "cursor-pointer",
        );
        btn.classList.add("bg-gray-400", "cursor-not-allowed");
    }
}
let wh_product_id = 0;

async function submitTransfer() {
    const warehouse_id = document.getElementById("to_location_select").value;
    const qty = document.getElementById("transfer_qty").value;

    const res = await fetch("/transfer-lot", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                .value,
            Accept: "application/json",
            "Content-Type": "application/json", // 🔥 must have
        },
        body: JSON.stringify({
            wh_product_id: wh_product_id,
            warehouse_id: warehouse_id,
            qty: qty,
        }),
    });

    const data = await res.json();

    if (res.ok) {
        // SUCCESS
        showToast({
            message: data.message || "Transfer completed",
            type: "success",
        });

        let select_wh = document.getElementById("warehouseTypeSelect");

        const warehouseId = select_wh.value;
        if (warehouseId === "All") {
            loadWarehouseStock(0, 1); // or handle All case
        } else {
            loadWarehouseStock(warehouseId, 1);
        }

        document.getElementById("transfer_modal").classList.add("hidden");
    } else {
        // ERROR
        showToast({
            message: data.message || "Transfer failed",
            type: "error",
        });
    }
}
function formatDate(dateStr) {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("en-GB");
}

function formatDateTime(dateStr) {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleString("en-GB");
}
async function loadItemLedgerEntries() {
    const tbody = document.getElementById("item_ledger_entry_table_body");

    tbody.innerHTML = `
        <tr>
            <td colspan="34" class="border px-3 py-4 text-center text-gray-500">Loading...</td>
        </tr>
    `;

    try {
        const response = await fetch("/item-ledger-entry");
        const data = await response.json();

        if (!data.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="34" class="border px-3 py-4 text-center text-gray-500">
                        No item ledger entry found
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data
            .map(
                (row) => `
            <tr class="hover:bg-gray-50 text-nowrap">
                <td class="border px-3 py-2 text-left">${row.entry_no ?? ""}</td>
                <td class="border px-3 py-2 text-center">${formatDate(row.posting_date)}</td>
                <td class="border px-3 py-2 text-left">${row.document_type ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.document_no ?? ""}</td>

                <td class="border px-3 py-2 text-left">${row.barcode ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.item_code ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.name ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.variant ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.description ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.unit ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.category_name ?? ""}</td>

                <td class="border px-3 py-2 text-left">${row.warehouse_name ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.lot ?? ""}</td>
                <td class="border px-3 py-2 text-center">${formatDate(row.expire_date)}</td>
                <td class="border px-3 py-2 text-right">${formatNumber(row.quantity)}</td>
                <td class="border px-3 py-2 text-right">${formatNumber(row.remaining_quantity)}</td>
                <td class="border px-3 py-2 text-left">${row.entry_type ?? ""}</td>

                <td class="border px-3 py-2 text-right">${formatNumber(row.unit_cost)} $</td>
                <td class="border px-3 py-2 text-right">${formatNumber(row.unit_price)} $</td>
                <td class="border px-3 py-2 text-right">${formatNumber(row.sell_price)} $</td>

                <td class="border px-3 py-2 text-right">${formatNumber(row.discount_percent)} %</td>
                <td class="border px-3 py-2 text-right">${formatNumber(row.discount_amount)} $</td>

                <td class="border px-3 py-2 text-right">${formatNumber(row.vat)} %</td>
                <td class="border px-3 py-2 text-right">${formatNumber(row.vat_amount)} $</td>

                <td class="border px-3 py-2 text-right">${formatNumber(row.line_amount)} $</td>
                <td class="border px-3 py-2 text-right">${formatNumber(row.net_amount)} $</td>
                <td class="border px-3 py-2 text-right">${formatNumber(row.grand_total_amount)} $</td>

                <td class="border px-3 py-2 text-left">${row.customer_id ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.customer_name ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.customer_phone ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.customer_address ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.vendor_id ?? ""}</td>
                <td class="border px-3 py-2 text-left">${row.payment_method ?? ""}</td>

                <td class="border px-3 py-2 text-left">${row.created_by ?? ""}</td>
                <td class="border px-3 py-2 text-center">${formatDateTime(row.created_at)}</td>
            </tr>
        `,
            )
            .join("");
    } catch (error) {
        console.error(error);
        tbody.innerHTML = `
            <tr>
                <td colspan="34" class="border px-3 py-4 text-center text-red-500">
                    Failed to load item ledger entry
                </td>
            </tr>
        `;
    }
}

function formatNumber(num) {
    return parseFloat(num ?? 0).toString();
}

document.getElementById("expense_data").addEventListener("click", function () {
    fetch("/expenses/latest")
        .then((response) => response.json())
        .then((res) => {
            const tbody = document.getElementById("expense_table_body");
            tbody.innerHTML = "";

            if (!res.status || res.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-3">
                            No expense found
                        </td>
                    </tr>
                `;
                return;
            }

            res.data.forEach((expense, index) => {
                tbody.innerHTML += `
                    <tr>
                        <td class="border px-3 py-2 text-right">${index + 1}</td>
                        <td class="border px-3 py-2 text-right">${formatDate(expense.expense_date)}</td>
                        <td class="border px-3 py-2 text-right">${expense.expense_code ?? ""}</td>
                        <td class="border px-3 py-2 text-right">${expense.expense_name}</td>
                        <td class="border px-3 py-2 text-right">${expense.qty}</td>

                         <td class="border px-3 py-2 text-right">${formatNumber(expense.unit_price)} $</td>
                         <td class="border px-3 py-2 text-right">${formatNumber(expense.amount)} $</td>
                   
                        <td class="border px-3 py-2 text-left">${expense.note ?? ""}</td>
                    </tr>
                `;
            });
        })
        .catch((error) => {
            console.error(error);
            alert("Failed to load expense data");
        });
});

window.addEventListener("payment-success", (e) => {
    const message = e.detail[0].message;

    showToast({
        message: message,
        type: "success",
    });

    // Ask before printing

    print_document("Receipt");

    // Clear after confirmation (whether printed or not)
    Livewire.dispatch("clearAll_after_payment");

    reloadProducts();
});
window.addEventListener("expense-success", (e) => {
    const message = e.detail[0].message;

    showToast({
        message: message,
        type: "success",
    });
});
window.addEventListener("ordered", (e) => {
    const message = e.detail[0].message;

    showToast({
        message: message,
        type: "success",
    });
});
window.addEventListener("view-cart-lots", (event) => {
    const { lots, product_name, product_id } = event.detail[0]; // <-- remove [0]

    // Grab original product image from main page
    const img = document.getElementById("product-image" + product_id); // optional, only if you have image IDs
    const display_img = document.getElementById("display_img2");

    if (img) {
        display_img.src = img.src; // copy image URL
    } else {
        display_img.src = "https://via.placeholder.com/150"; // fallback
    }

    const modalBody = document.getElementById("viewLotModalBody");
    const modalTitle = document.getElementById("view-lot-title");
    modalBody.innerHTML = "";

    modalTitle.textContent = `Tracked Lots for: ${product_name}`;

    if (!lots.length) {
        modalBody.innerHTML =
            "<p class='text-gray-500'>No lots tracked yet.</p>";
    } else {
        // Header
        const header = document.createElement("div");
        header.className =
            "grid grid-cols-5 gap-2 font-semibold border-b pb-1 text-gray-700";
        header.innerHTML = `

         <div>Warehouse</div>
            <div>Lot No</div>
            <div>Qty</div>
            <div>Stock</div>
            <div>Expire</div>
        `;
        modalBody.appendChild(header);

        // Rows
        lots.forEach((lot) => {
            const row = document.createElement("div");
            row.className =
                "grid grid-cols-5 gap-2 items-center p-1 bg-gray-50 rounded";

            let expireClass = "";
            if (lot.expire && new Date(lot.expire) < new Date()) {
                expireClass = "text-red-500 font-bold";
            }

            row.innerHTML = `
                <span class="text-left px-2">${lot.warehouse}</span>
                <span class="text-left px-2">${lot.lot}</span>
                <span class="text-center px-2">${lot.qty}</span>
                <span class="text-center px-2">${lot.stock}</span>
                <span class="text-center px-2 text-nowrap ${expireClass}">${lot.expire}</span>
            `;
            modalBody.appendChild(row);
        });
    }

    // Show modal
    document.getElementById("viewLotModal").classList.remove("hidden");
});
window.addEventListener("get-date", (event) => {
    const input_document_date = document.getElementById("document_dateInput");

    // Livewire sends full ISO string, we need YYYY-MM-DD
    const documentDate = event.detail[0].invoice_date.split("T")[0]; // "2026-03-19"

    input_document_date.value = documentDate;

    reciept_no = event.detail[0].invoice_no;
});
window.addEventListener("trigger-print", (e) => {
    print_document("Receipt");
    Livewire.dispatch("clearCart_no_message");
});
window.addEventListener("cart-loaded", (e) => {
    document.querySelector("#count_cart_input").value = 1;
    initializePayment();
    updatePayment();
    print("Receipt");
});

window.addEventListener("serve-table", (e) => {
    showToast({
        message: e.detail[0].message,
        type: "success",
    });
    showTableModal(0, "ALL");
});

let reciept_no = "NA";
window.addEventListener("get-reciept-no", (e) => {
    reciept_no = e.detail[0].invoice_number;
});

window.addEventListener("clear-customer", (e) => {
    document.querySelector("#customerValue").value = "";
    document.querySelector("#customerSearch").value = "";
});

window.addEventListener("update-customer-input", (e) => {
    document.querySelector("#customerValue").value = e.detail[0].code;
    document.querySelector("#customerSearch").value = e.detail[0].display;
});
let document_no = "NA";
window.addEventListener("get-document", (e) => {
    document_no = e.detail[0].document_no;
});
window.addEventListener("cart-cleared", (e) => {
    current_discount = 0;
    messsage = e.detail[0].message;
    showToast({
        message: messsage,
        type: "success",
    });
    document.querySelector("#customerValue").value = "";
    document.querySelector("#customerSearch").value = "";
});

window.addEventListener("payment-error", (e) => {
    const message = e.detail[0].message;

    showToast({
        message: message,
        type: "error",
    });

    // Ask before printing
});

window.addEventListener("no-stock", (e) => {
    const message = e.detail[0].message;

    showToast({
        message: message,
        type: "error",
    });
});

// function openSaleOrderModal() {
//     const modal = document.getElementById("default-modal-sales-order-list");
//     modal.style.display = "flex";
// }

// function closeSaleOrderModal() {
//     const modal = document.getElementById("default-modal-sales-order-list");
//     modal.style.display = "none";
// }

document.getElementById("sale_order_status").addEventListener("change", () => {
    loadSaleOrders(1);
});
function openSaleOrderModal() {
    const modal = document.getElementById("default-modal-sales-order-list");
    if (!modal) return;

    modal.classList.remove("hidden");

    loadSaleOrders(1);
}

function closeSaleOrderModal() {
    const modal = document.getElementById("default-modal-sales-order-list");
    if (!modal) return;

    modal.classList.add("hidden");
}
document
    .getElementById("sale_order_payment_status")
    .addEventListener("change", () => {
        loadSaleOrders(1);
    });

document.getElementById("sale_order_search").addEventListener("input", () => {
    loadSaleOrders(1);
});

function loadSaleOrders(page = 1) {
    console.log(123);
    let status = document.getElementById("sale_order_status").value;
    let paymentStatus = document.getElementById(
        "sale_order_payment_status",
    ).value;

    const modal = document.getElementById("default-modal-sales-order-list");

    if (!modal) {
        console.error("Modal not found: default-modal-sales-order-list");
        return;
    }

    // open modal
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    let search = document.getElementById("sale_order_search").value;

    fetch(
        `/get-sale-orders?page=${page}&status=${status}&payment_status=${paymentStatus}&search=${search}`,
    )
        .then((res) => res.json())
        .then((res) => {
            let tbody = document.getElementById("Table-sale-order-list");
            tbody.innerHTML = "";

            if (!res.data || res.data.length === 0) {
                tbody.innerHTML = `
        <tr>
            <td colspan="21" class="text-center py-6 text-gray-500">
                No Sale Orders Found 😢
            </td>
        </tr>
    `;

                document.getElementById(
                    "paginationContainer_sale_order",
                ).innerHTML = "";
                document.getElementById("pageInfo_sale_order").textContent = "";

                return;
            }

            res.data.forEach((row, index) => {
                tbody.innerHTML += `
                <tr onclick="selectSaleOrderRow(this, ${row.id})"
                        ondblclick="viewSaleOrderLine(${row.id})"
                        class="cursor-pointer hover:bg-gray-50 text-nowrap">

                        <td class="px-4 py-2">
                            <input type="checkbox" class="sale-order-checkbox pointer-events-none">
                        </td>

                        <td class="px-4 py-3">${index + 1}</td>
                        <td class="px-4 py-3">${row.document_no ?? ""}</td>
                        <td class="px-4 py-3 text-center">${row.posting_date ?? ""}</td>
                        <td class="px-4 py-3 text-center">${row.order_date ?? ""}</td>
                        <td class="px-4 py-3 text-center">${row.delivery_date ?? ""}</td>

                        <td class="px-4 py-3">${row.contact_name ?? ""}</td>
                        <td class="px-4 py-3">${row.phone ?? ""}</td>
                        <td class="px-4 py-3">${row.address ?? ""}</td>


                         <td class="px-4 py-3 text-right">$${parseFloat(row.total_amount ?? 0).toFixed(2)}</td>
                        <td class="px-4 py-3 text-right">$${parseFloat(row.vat_amount ?? 0).toFixed(2)}</td>
                        <td class="px-4 py-3 text-right">$${parseFloat(row.discount_amount ?? 0).toFixed(2)}</td>
                   
                                <td class="px-4 py-3 text-right">$${parseFloat(row.grand_total ?? 0).toFixed(2)}</td>
                        <td class="px-4 py-3 text-right">$${parseFloat(row.paid_amount ?? 0).toFixed(2)}</td>
                    

               <td class="px-4 py-3 text-right ${
                   parseFloat(row.balance_amount ?? 0) > 0
                       ? "text-red-500"
                       : "text-green-500"
               }">
    ${parseFloat(row.balance_amount ?? 0).toFixed(2)}
</td>
                        <td class="px-4 py-3 text-right text-nowrap">${getStatusBadge(row.status)}</td>
                        <td class="px-4 py-3 text-right text-nowrap">${getPaymentBadge(row.payment_status)}</td>
                        <td class="px-4 py-3 text-center text-nowrap">${getDeliveryBadge(row.delivery_status)}</td>
                        <td class="px-4 py-3 text-center">${row.delivery_info ?? ""}</td>
                        <td class="px-4 py-3">${row.driver_name ?? ""}</td>
                        <td class="px-4 py-3">${row.driver_phone ?? ""}</td>
                    </tr>`;
            });
            renderSaleOrderPagination(res);
        })
        .catch((err) => {
            let tbody = document.getElementById("Table-sale-order-list");
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-6 text-red-500">
                        Error loading data ⚠️
                    </td>
                </tr>
            `;
            console.error(err);
        });
}
function getDeliveryBadge(status) {
    const styles = {
        Pending: {
            text: "Pending",
            icon: "⏳",
            class: "bg-amber-100 text-amber-700",
        },
        Processing: {
            text: "Processing",
            icon: "⚙️",
            class: "bg-sky-100 text-sky-700",
        },
        Shipped: {
            text: "Shipped",
            icon: "🚚",
            class: "bg-violet-100 text-violet-700",
        },
        Delivered: {
            text: "Delivered",
            icon: "✅",
            class: "bg-emerald-100 text-emerald-700",
        },
        Cancelled: {
            text: "Cancelled",
            icon: "❌",
            class: "bg-rose-100 text-rose-700",
        },
        Returned: {
            text: "Returned",
            icon: "↩️",
            class: "bg-orange-100 text-orange-700",
        },
        "N/A": {
            text: "N/A",
            icon: "⚪",
            class: "bg-gray-100 text-gray-700",
        },
    };

    const item = styles[status] || {
        text: "Unknown",
        icon: "❔",
        class: "bg-gray-100 text-gray-700",
    };

    return `
        <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full ${item.class}">
            <span>${item.icon}</span>
            <span>${item.text}</span>
        </span>
    `;
}
function renderSaleOrderPagination(res) {
    const container = document.getElementById("paginationContainer_sale_order");
    const pageInfo = document.getElementById("pageInfo_sale_order");

    container.innerHTML = "";

    if (!res.links) return;

    pageInfo.textContent = `Page ${res.current_page} of ${res.last_page} | Total ${res.total}`;

    res.links.forEach((link) => {
        let label = link.label
            .replace("&laquo; Previous", "Prev")
            .replace("Next &raquo;", "Next");

        const btn = document.createElement("button");
        btn.innerHTML = label;
        btn.disabled = !link.url;

        btn.className = `
            px-3 py-1 rounded-lg border text-sm
            ${
                link.active
                    ? "bg-blue-600 text-white border-blue-600"
                    : "bg-white text-gray-700 hover:bg-gray-100"
            }
            ${!link.url ? "opacity-50 cursor-not-allowed" : ""}
        `;

        if (link.url) {
            const url = new URL(link.url);
            const page = url.searchParams.get("page");

            btn.onclick = () => loadSaleOrders(page);
        }

        container.appendChild(btn);
    });
}
function getStatusBadge(status) {
    switch ((status || "").toLowerCase()) {
        case "draft":
            return `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-700 border border-slate-200 shadow-sm">
                        📝 Draft
                    </span>`;

        case "ordered":
            return `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-700 border border-blue-200 shadow-sm">
                        📦 Ordered
                    </span>`;

        case "deposit":
            return `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-amber-50 text-amber-700 border border-amber-200 shadow-sm">
                        💰 Deposit
                    </span>`;

        case "completed":
            return `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                        ✅ Completed
                    </span>`;

        case "cancelled":
            return `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-rose-50 text-rose-700 border border-rose-200 shadow-sm">
                        ❌ Cancelled
                    </span>`;
        case "returned":
            return `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-purple-50 text-purple-700 border border-purple-200 shadow-sm">
                        ↩️ Returned
                    </span>`;
        default:
            return `<span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-700">${status}</span>`;
    }
}

function getPaymentBadge(status) {
    switch ((status || "").toLowerCase()) {
        case "unpaid":
            return `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-rose-50 text-rose-700 border border-rose-200 shadow-sm">
                        💸 Unpaid
                    </span>`;

        case "partial":
            return `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-amber-50 text-amber-700 border border-amber-200 shadow-sm">
                        🌓 Partial
                    </span>`;

        case "paid":
            return `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                        💵 Paid
                    </span>`;

        case "refunded":
            return `<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-700 border border-blue-200 shadow-sm">
                        ↩️ Refunded
                    </span>`;

        default:
            return `<span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-700">${status}</span>`;
    }
}
function Save_Sale_Order() {
    let input_count_cart = document.getElementById("count_cart_input");
    let count_cart = input_count_cart.value;
    let new_order = document.querySelector("#new_order");
    if (new_order) {
        new_order.style.display = "block";
    }

    let update_order = document.querySelector("#update_order");
    if (update_order) {
        update_order.style.display = "none";
    }
    if (count_cart == 0) {
        showToast({
            message: "Cart is Empty.",
            type: "error",
        });
        return;
    }
    const payUSDInput = document.getElementById("so_pay_usd");
    const payOtherInput = document.getElementById("so_pay_other");

    // Reset input
    payUSDInput.value = 0;
    payOtherInput.value = 0;
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

    document.querySelector("#so_display_pay_amount").value =
        total_amount + " $";
    document.querySelector("#so_display_pay_amount_converted").value =
        converted_total_amount + " " + currency_display_symbol.value;
    // set today date
    const today = new Date().toISOString().split("T")[0];

    document.getElementById("so_document_dateInput").value = today;
    document.getElementById("so_order_dateInput").value = today;
    document.getElementById("so_delivery_dateInput").value = today;
    // open modal
    document
        .getElementById("default-modal-sales-order-save")
        .classList.remove("hidden");
}
function moneyNumber(value) {
    return (
        parseFloat(
            String(value || "0")
                .replace(/,/g, "")
                .replace(/[^\d.-]/g, ""),
        ) || 0
    );
}
function validateSaleOrderPayment(e = null) {
    const totalAmount = moneyNumber(
        document.querySelector("#so_display_pay_amount")?.value
    );

    const oldPaidAmount = moneyNumber(
        document.querySelector("#paid_amount")?.value
    );

    const payUSDInput = document.getElementById("so_pay_usd");
    const payOtherInput = document.getElementById("so_pay_other");

    const payUSD = moneyNumber(payUSDInput?.value);
    const payOther = moneyNumber(payOtherInput?.value);

    const factor =
        moneyNumber(document.querySelector("#currency_display_factor")?.value) || 4000;

    const newPaidAmount = payUSD + payOther / factor;

    const totalPaidAmount = oldPaidAmount + newPaidAmount;
    const remainUSD = totalAmount - totalPaidAmount;

    if (remainUSD < -0.0001) {
        showToast({
            message: "Payment exceeds balance!",
            type: "error",
        });

        if (e && e.target.id === "so_pay_usd") {
            payUSDInput.value = 0;
        }

        if (e && e.target.id === "so_pay_other") {
            payOtherInput.value = 0;
        }

        updateSaleOrderRemaining();
        return false;
    }

    updateSaleOrderRemaining();
    return true;
}
function updateSaleOrderRemaining() {
    const totalAmount = moneyNumber(
        document.querySelector("#so_display_pay_amount")?.value
    );

    const oldPaidAmount = moneyNumber(
        document.querySelector("#paid_amount")?.value
    );

    const payUSD = moneyNumber(
        document.getElementById("so_pay_usd")?.value
    );

    const payOther = moneyNumber(
        document.getElementById("so_pay_other")?.value
    );

    const factor =
        moneyNumber(
            document.querySelector("#currency_display_factor")?.value
        ) || 4000;

    const newPaidAmount = payUSD + payOther / factor;

    const totalPaidAmount = oldPaidAmount + newPaidAmount;

    let remainUSD = totalAmount - totalPaidAmount;
    let remainRiel = Math.round(remainUSD * factor);

    // ignore tiny rounding issue
    if (Math.abs(remainRiel) <= 50) {
        remainUSD = 0;
        remainRiel = 0;
    }

    remainUSD = Math.max(remainUSD, 0);
    remainRiel = Math.max(remainRiel, 0);

    document.getElementById("so_need_more_usd").textContent =
        remainUSD.toFixed(2) + " $";

    document.getElementById("so_need_more_other").textContent =
        remainRiel.toLocaleString() + " ៛";
}
function openDatePromt_Modal(onConfirm) {
    const payUSDInput = document.getElementById("pay_usd");
    const payOtherInput = document.getElementById("pay_other");

    // Reset input
    payUSDInput.value = 0;
    payOtherInput.value = 0;

    const modal = document.getElementById("DatePromptModal");

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

    // initailize each click
    document.querySelector("#paid_amount").value = 0;

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
    };
}
let selectedSaleOrderId = null;

function selectSaleOrderRow(rowElement, id) {
    selectedSaleOrderId = id;

    // uncheck all
    document.querySelectorAll(".sale-order-checkbox").forEach((cb) => {
        cb.checked = false;
    });

    // check current
    const checkbox = rowElement.querySelector(".sale-order-checkbox");
    if (checkbox) checkbox.checked = true;
}
function viewSelectedSaleOrderLine() {
    if (!selectedSaleOrderId) {
        showToast({
            message: "Please select a sale order first",
            type: "error",
        });

        return;
    }

    // Load data first
    viewSaleOrderLine(selectedSaleOrderId);
}

// Close modal
function closeSaleOrderLineModal() {
    document.getElementById("saleOrderLineModal").classList.add("hidden");
}

function Load_order(e) {
    if (!currentSaleOrderId) {
        alert("No sale order selected");
        return;
    }
    // check cart Logic
    let input_count_cart = document.getElementById("count_cart_input");
    let count_cart = input_count_cart.value;
    if (count_cart > 0) {
        showToast({
            message: "Cart is not Empty.",
            type: "error",
        });
        return;
    }

    Livewire.dispatch("load-sale-order-to-cart", {
        saleOrderId: currentSaleOrderId,
    });

    closeSaleOrderLineModal();
}

document
    .getElementById("so_customer_type")
    .addEventListener("change", function () {
        const deliverySection = document.getElementById("delivery_section");

        if (this.value === "At-Delivery") {
            deliverySection.classList.remove("hidden");
        } else {
            deliverySection.classList.add("hidden");

            document.getElementById("so_delivery_dateInput").value = "";
            document.getElementById("so_delivery_status").value = "PENDING";
            document.getElementById("so_delivery_info_status").value = "";
            document.getElementById("so_driver_name").value = "";
            document.getElementById("so_driver_phone").value = "";
        }
    });

function changeSaleOrderStatus(status) {
    const select = document.getElementById("sale-order-status");

    let className = "";

    switch (status) {
        case "Quotation":
            className = "bg-gray-100 text-gray-700";
            break;
        case "Ordered":
            className = "bg-yellow-100 text-yellow-700";
            break;
        case "Deposit":
            className = "bg-blue-100 text-blue-700";
            break;
        case "completed":
            className = "bg-green-100 text-green-700";
            break;
        case "cancelled":
            className = "bg-red-100 text-red-700";
            break;
    }

    select.className = `px-3 py-1 rounded-full text-sm font-semibold border-none outline-none ${className}`;

    console.log("new status:", status);

    // fetch('/update-sale-order-status', ...)
}

let currentSaleOrderId = null;

async function viewSaleOrderLine(id) {
    try {
        currentSaleOrderId = id;

        const modal = document.getElementById("saleOrderLineModal");
        modal.classList.remove("hidden");
        modal.setAttribute("aria-hidden", "false");

        const res = await fetch(`/sale-order-lines/${id}`);
        if (!res.ok) throw new Error("Failed to load sale order");

        const data = await res.json();
        const header = data.header ?? {};

        const setText = (id, value = "-") => {
            const el = document.getElementById(id);
            if (el) el.textContent = value || "-";
        };
        document.querySelector("#sale_order_id").value = id;

        setText("sale-order-no", header.document_no);
        setText("sale-order-created-by", header.created_by);
        setText("sale-order-posting-date", header.posting_date);

        setText("sale-order-customer", header.contact_name);
        setText("sale-order-phone", header.phone);
        setText("sale-order-address", header.address);

        setText("sale-order-payment-method", header.payment_method);
        setText("sale-order-delivery-date", header.delivery_date);

        setText("sale-order-delivery-status", header.delivery_status);
        setText("sale-order-delivery-info", header.delivery_info);
        setText("sale-order-driver-name", header.driver_name);
        setText("sale-order-driver-phone", header.driver_phone);

        const deliveryBox = document.getElementById("sale-order-delivery-box");
        if (deliveryBox) {
            const isDelivery =
                header.customer_type === "At-Delivery" ||
                header.customer_type === "DELIVERY";

            deliveryBox.classList.toggle("hidden", !isDelivery);
        }

        const statusEl = document.getElementById("sale-order-status");
        if (statusEl) {
            statusEl.value = header.status ?? "Ordered";
            changeSaleOrderStatus(statusEl.value);
        }

        const paymentEl = document.getElementById("sale-payment-status");
        if (paymentEl) {
            paymentEl.textContent = header.payment_status ?? "unpaid";
            setStatusColor(
                "sale-payment-status",
                header.payment_status ?? "unpaid",
            );
        }

        setText(
            "sale-order-total",
            "$" + parseFloat(header.total_amount ?? 0).toFixed(2),
        );
        setText(
            "sale-order-discount",
            "$" + parseFloat(header.discount_amount ?? 0).toFixed(2),
        );
        setText(
            "sale-order-vat",
            "$" + parseFloat(header.vat_amount ?? 0).toFixed(2),
        );
        setText(
            "sale-order-grand-total",
            "$" + parseFloat(header.grand_total ?? 0).toFixed(2),
        );

        const factor = parseFloat(
            document.querySelector("#currency_display_factor")?.value || 1,
        );

        const currency =
            document.querySelector("#currency_display_symbol")?.value || "$";

        let convertedTotal = (header.grand_total ?? 0) * factor;

        if (currency === "៛") {
            convertedTotal = parseFloat(convertedTotal).toFixed(0);
        } else {
            convertedTotal = parseFloat(convertedTotal).toFixed(2);
        }

        setText(
            "sale-order-grand-total-converted",
            "តម្លៃសរុប " + currency + " : " + convertedTotal + " " + currency,
        );
        if (factor !== 1) {
            setText(
                "currency-rate-info",
                "1$ = " + factor.toLocaleString() + " " + currency,
            );
        } else {
            setText("currency-rate-info", "");
        }
        let html = "";

        (data.lines ?? []).forEach((line, index) => {
            html += `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">${index + 1}</td>
                    <td class="px-4 py-2">${line.item_code ?? ""}</td>
                    <td class="px-4 py-2">${line.name ?? ""}</td>
                    <td class="px-4 py-2 text-right">${parseFloat(line.quantity ?? 0).toFixed(2)}</td>
                    <td class="px-4 py-2 text-right">$${parseFloat(line.sell_price ?? 0).toFixed(2)}</td>
                    <td class="px-4 py-2 text-right">$${parseFloat(line.sub_total ?? 0).toFixed(2)}</td>
                    <td class="px-4 py-2 text-right">$${parseFloat(line.discount_amount ?? 0).toFixed(2)}</td>
                    <td class="px-4 py-2 text-right">$${parseFloat(line.vat_amount ?? 0).toFixed(2)}</td>
                    <td class="px-4 py-2 text-right">$${parseFloat(line.grand_total_amount ?? 0).toFixed(2)}</td>
                </tr>
            `;
        });

        document.getElementById("sale-line-data").innerHTML = html;
    } catch (e) {
        console.error(e);
        alert("Error loading sale order");
    }
}
function openSaleLine() {
    const modal = document.getElementById("saleOrderLineModal");
    modal.classList.remove("hidden");
    modal.setAttribute("aria-hidden", "false");
}
function setStatusColor(id, status) {
    const el = document.getElementById(id);
    if (!el) return;

    let className = "bg-gray-100 text-gray-700";

    switch (status) {
        case "unpaid":
            className = "bg-red-100 text-red-700";
            break;
        case "partial":
            className = "bg-yellow-100 text-yellow-700";
            break;
        case "paid":
            className = "bg-green-100 text-green-700";
            break;
        case "N/A":
            className = "bg-gray-100 text-gray-700";
            break;
    }

    el.className = `px-3 py-1 rounded-full text-sm font-medium ${className}`;
}

function update_sale_order() {
    document
        .getElementById("default-modal-sales-order-save")
        .classList.remove("hidden");
}

function Confirm_Save_Sale_Order(document_status) {
    const totalAmount = parseFloat(
        document.querySelector("#total_amount")?.value || 0,
    );

    const payUSD = parseFloat(
        document.getElementById("so_pay_usd")?.value || 0,
    );

    const payOther = parseFloat(
        document.getElementById("so_pay_other")?.value || 0,
    );

    const factor = parseFloat(
        document.querySelector("#currency_display_factor")?.value || 1,
    );

    const paidAmount = payUSD + payOther / factor;

    let paymentData = {
        // Dates
        document_date:
            document.getElementById("so_document_dateInput")?.value ?? null,
        order_date:
            document.getElementById("so_order_dateInput")?.value ?? null,
        delivery_date:
            document.getElementById("so_delivery_dateInput")?.value ?? null,

        // Payment
        paymentMethod:
            document.getElementById("so_payment_method")?.value ?? null,
        paid_amount: paidAmount,
        deposit_amount: paidAmount,
        total_amount: totalAmount,

        // Customer
        customer_type:
            document.getElementById("so_customer_type")?.value ?? null,
        customer_id:
            document.getElementById("so_customer_id_info")?.value ?? null,
        customer_name:
            document.getElementById("so_customer_name_info")?.value ?? null,
        customer_phone:
            document.getElementById("so_customer_phone_info")?.value ?? null,
        customer_address:
            document.getElementById("so_customer_address_info")?.value ?? null,

        // Delivery
        delivery_status:
            document.getElementById("so_delivery_status")?.value ?? null,
        delivery_info:
            document.getElementById("so_delivery_info_status")?.value ?? null,
        driver_name: document.getElementById("so_driver_name")?.value ?? null,
        driver_phone: document.getElementById("so_driver_phone")?.value ?? null,
        status: document_status,
        // Optional
        remark: document.getElementById("so_remark_invoice")?.value ?? null,
    };

    console.log("Payment Data:", paymentData);

    Livewire.dispatch("confirmSaleOrder", [paymentData]);
    document.getElementById("so_customer_type").value = "";
    document.getElementById("so_delivery_dateInput").value = "";
    document.getElementById("so_delivery_status").value = "PENDING";
    document.getElementById("so_delivery_info_status").value = "";
    document.getElementById("so_driver_name").value = "";
    document.getElementById("so_driver_phone").value = "";

    // Hide delivery section again
    document.getElementById("delivery_section").classList.add("hidden");
    document
        .getElementById("default-modal-sales-order-save")
        .classList.add("hidden");
}

window.addEventListener("load-sale-order", (e) => {
    const message = e.detail[0].message;
    fillSaleOrderModal(e.detail[0].header);
    showToast({
        message: message,
        type: "success",
    });
});
window.addEventListener("expense_item_prevented", (e) => {
    const message = e.detail[0].message;

    showToast({
        message: message,
        type: "error",
    });
});
window.addEventListener("product_item_prevented", (e) => {
    const message = e.detail[0].message;

    showToast({
        message: message,
        type: "error",
    });
});
function fillSaleOrderModal(data = {}) {
    document.getElementById("so_sale_order_id").value = data.id ?? "";

    document.getElementById("so_document_dateInput").value =
        data.posting_date ?? data.document_date ?? "";

    document.getElementById("so_order_dateInput").value = data.order_date ?? "";
    document.getElementById("so_delivery_dateInput").value =
        data.delivery_date ?? "";

    const factor = parseFloat(
        document.querySelector("#currency_display_factor")?.value || 1,
    );
    const currency =
        document.querySelector("#currency_display_symbol")?.value || "$";

    const grandTotal =
        parseFloat(data.grand_total ?? data.total_amount ?? 0) || 0;

    let convertedTotal = grandTotal * factor;
    convertedTotal =
        currency === "៛"
            ? convertedTotal.toFixed(0)
            : convertedTotal.toFixed(2);

    document.getElementById("so_payment_method").value =
        data.payment_method ?? "ABA";

    document.getElementById("paid_amount").value = data.paid_amount ?? 0;

    document.getElementById("so_pay_usd").value = 0;
    document.getElementById("so_pay_other").value = 0;

    document.getElementById("so_display_pay_amount").value = grandTotal;
    document.getElementById("so_display_pay_amount_converted").value =
        convertedTotal + " " + currency;

    document.getElementById("so_customer_type").value =
        data.customer_type ?? "Take-Away";
    document.getElementById("so_customer_id_info").value =
        data.customer_id ?? "";
    document.getElementById("so_customer_name_info").value =
        data.contact_name ?? data.customer_name ?? "";
    document.getElementById("so_customer_phone_info").value =
        data.phone ?? data.customer_phone ?? "";
    document.getElementById("so_customer_address_info").value =
        data.address ?? data.customer_address ?? "";

    if (document.querySelector("#customerSearch")) {
        document.querySelector("#customerSearch").value =
            data.contact_name ?? data.customer_name ?? "";
    }

    document.getElementById("so_delivery_status").value =
        data.delivery_status ?? "PENDING";
    document.getElementById("so_delivery_info_status").value =
        data.delivery_info ?? "";
    document.getElementById("so_driver_name").value = data.driver_name ?? "";
    document.getElementById("so_driver_phone").value = data.driver_phone ?? "";

    document.getElementById("so_remark_invoice").value =
        data.remarks ?? data.remark ?? "";

    if (data.customer_type === "At-Delivery") {
        document.getElementById("delivery_section").classList.remove("hidden");
    } else {
        document.getElementById("delivery_section").classList.add("hidden");
    }

    if (typeof validateSaleOrderPayment === "function") {
        validateSaleOrderPayment();
    }
    document
        .getElementById("default-modal-sales-order-save")
        .classList.add("hidden");
    document.querySelector("#new_order").style.display = "none";
    document.querySelector("#update_order").style.display = "block";
}

function Confirm_update_Sale_Order() {
    const factor = parseFloat(
        document.querySelector("#currency_display_factor")?.value || 1,
    );

    const payUSD = parseFloat(
        document.getElementById("so_pay_usd")?.value || 0,
    );
    const payOther = parseFloat(
        document.getElementById("so_pay_other")?.value || 0,
    );

    const newPaidAmount = payUSD + payOther / factor;

    let paymentData = {
        sale_order_id:
            document.getElementById("so_sale_order_id")?.value ?? null,

        // Dates
        document_date:
            document.getElementById("so_document_dateInput")?.value ?? null,
        order_date:
            document.getElementById("so_order_dateInput")?.value ?? null,
        delivery_date:
            document.getElementById("so_delivery_dateInput")?.value ?? null,

        // Payment
        paymentMethod:
            document.getElementById("so_payment_method")?.value ?? null,
        new_paid_amount: newPaidAmount,

        // Customer
        customer_type:
            document.getElementById("so_customer_type")?.value ?? null,
        customer_id:
            document.getElementById("so_customer_id_info")?.value ?? null,
        customer_name:
            document.getElementById("so_customer_name_info")?.value ?? null,
        customer_phone:
            document.getElementById("so_customer_phone_info")?.value ?? null,
        customer_address:
            document.getElementById("so_customer_address_info")?.value ?? null,

        // Delivery
        delivery_status:
            document.getElementById("so_delivery_status")?.value ?? null,
        delivery_info:
            document.getElementById("so_delivery_info_status")?.value ?? null,
        driver_name: document.getElementById("so_driver_name")?.value ?? null,
        driver_phone: document.getElementById("so_driver_phone")?.value ?? null,

        // Remark
        remark: document.getElementById("so_remark_invoice")?.value ?? null,
    };

    document
        .getElementById("default-modal-sales-order-save")
        .classList.add("hidden");
    console.log(paymentData);
    Livewire.dispatch("confirmUpdateSaleOrder", {
        payload: paymentData,
    });
}
function openExpenseModal() {
    document.getElementById("expenseModal").classList.remove("hidden");

    let today = new Date().toISOString().split("T")[0];
    document.getElementById("expenseDate").value = today;
}

function closeExpenseModal() {
    document.getElementById("expenseModal").classList.add("hidden");
}

function confirmExpensePayment() {
    const selectedDate = document.getElementById("expenseDate").value;

    if (!selectedDate) {
        alert("Please select expense date");
        return;
    }

    pay_expense(selectedDate);

    closeExpenseModal();
}

function pay_expense(payload) {
    Livewire.dispatch("payment-expenses", {
        payload: payload,
    });
}
