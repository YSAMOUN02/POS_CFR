const Sales_btn = document.getElementById("Sales");
// Confirm purchasing
Sales_btn.addEventListener("click", () => {
    window.location.href = "/Sale";
});

window.addEventListener("error", (e) => {
    console.log("Error event received:", e);
    const message = e.detail[0].message;
    console.error(message);
    showToast({
        message: message,
        type: "error",
    });
});
window.addEventListener("success", (e) => {
    const message = e.detail[0].message;
    console.error(message);
    showToast({
        message: message,
        type: "success",
    });
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
function hideToast() {
    const toast = document.getElementById("toastMessage");
    toast.classList.add("hidden");

    // Optional: reset icon and text
    document.getElementById("toastText").innerText = "";
    document.getElementById("toastIcon").innerText = "✔️";
}

async function addVendor() {
    const form = document.getElementById("AddVendorForm");
    const btn = document.querySelector(
        '#AddVendorForm button[onclick="addVendor()"]',
    );

    const originalText = btn.innerHTML;

    // Disable button
    btn.disabled = true;
    btn.innerHTML = "Saving...";
    btn.classList.add("opacity-50", "cursor-not-allowed");

    const formData = new FormData(form);

    try {
        const response = await fetch("/vendors", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                    .value,
                Accept: "application/json",
            },
            body: formData,
        });

        const data = await response.json();

        if (response.ok) {
            showToast({
                message: "Vendor added successfully!",
                type: "success",
            });
            loadVendors(1);
            form.reset();

            // Optional close modal after success
            // document.querySelector('[data-modal-hide="default-modal-vendor"]').click();
        } else {
            showToast({
                message: data.message || "Failed to add vendor",
                type: "error",
            });
        }
    } catch (error) {
        console.error(error);

        showToast({
            message: "Server error",
            type: "error",
        });
    } finally {
        // Enable button again
        btn.disabled = false;
        btn.innerHTML = originalText;
        btn.classList.remove("opacity-50", "cursor-not-allowed");
    }
}

let vendorSearchTimeout = null;

function handleVendorSearchInput() {
    clearTimeout(vendorSearchTimeout);
    vendorSearchTimeout = setTimeout(() => {
        loadVendors(1);
    }, 400);
}
async function loadVendors(page = 1) {
    const search = document.getElementById("vendorSearchInput")?.value || "";
    const activeOnly = document.getElementById("vendorSearchCheckbox")?.checked
        ? 1
        : 0;
    const tbody = document.getElementById("vendor-table-body");
    const pageInfo = document.getElementById("vendorPageInfo");

    tbody.innerHTML = `
        <tr>
            <td colspan="12" class="text-center px-4 py-6">Loading...</td>
        </tr>
    `;

    try {
        const url = `/vendors/list?page=${page}&search=${encodeURIComponent(search)}&active=${activeOnly}`;

        const response = await fetch(url, {
            headers: {
                Accept: "application/json",
            },
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || "Failed to fetch vendors");
        }

        const vendors = result.data.data || [];
        const pagination = result.data;

        if (vendors.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center px-4 py-6 text-gray-500">No vendors found</td>
                </tr>
            `;
        } else {
            tbody.innerHTML = vendors
                .map(
                    (vendor) => `
                <tr class="border-b border-default hover:bg-neutral-secondary-medium"     onclick="selectVendorRow(this, ${vendor.id})" >
           <td class="px-4 py-3">
        <input type="checkbox"
            class="vendor-checkbox pointer-events-none"
            value="${vendor.id}">
    </td>

                    <td class="px-4 py-3">${vendor.id ?? ""}</td>
                    <td class="px-4 py-3">${vendor.code ?? ""}</td>
                    <td class="px-4 py-3">${vendor.name ?? ""}</td>
                    <td class="px-4 py-3">${vendor.contact_person ?? ""}</td>
                    <td class="px-4 py-3">${vendor.phone1 ?? ""}</td>
                    <td class="px-4 py-3">${vendor.phone2 ?? ""}</td>
                    <td class="px-4 py-3">${vendor.email ?? ""}</td>
                    <td class="px-4 py-3">${vendor.country ?? ""}</td>
                    <td class="px-4 py-3">${vendor.city ?? ""}</td>
                    <td class="px-4 py-3">${vendor.website ?? ""}</td>
                    <td class="px-4 py-3">
                        ${
                            vendor.status == 1
                                ? '<span class="text-green-600 font-medium">Active</span>'
                                : '<span class="text-red-600 font-medium">Inactive</span>'
                        }
                    </td>
                </tr>
            `,
                )
                .join("");
        }

        renderVendorPagination(pagination);
        pageInfo.textContent =
            activeOnly == 1
                ? `${pagination.total} Active Vendors  `
                : `${pagination.total} Inactive Vendors  `;
    } catch (error) {
        console.error(error);
        tbody.innerHTML = `
            <tr>
                <td colspan="12" class="text-center px-4 py-6 text-red-500">Error loading vendors</td>
            </tr>
        `;
    }
}
function renderVendorPagination(pagination) {
    const container = document.getElementById("vendorPaginationContainer");
    container.innerHTML = "";

    if (!pagination.last_page || pagination.last_page <= 1) return;

    let buttons = "";

    buttons += `
        <button ${pagination.current_page === 1 ? "disabled" : ""}
            onclick="loadVendors(${pagination.current_page - 1})"
            class="px-3 py-1 border rounded ${pagination.current_page === 1 ? "opacity-50 cursor-not-allowed" : ""}">
            Prev
        </button>
    `;

    for (let i = 1; i <= pagination.last_page; i++) {
        buttons += `
            <button onclick="loadVendors(${i})"
                class="px-3 py-1 border rounded ${i === pagination.current_page ? "bg-brand text-white" : ""}">
                ${i}
            </button>
        `;
    }

    buttons += `
        <button ${pagination.current_page === pagination.last_page ? "disabled" : ""}
            onclick="loadVendors(${pagination.current_page + 1})"
            class="px-3 py-1 border rounded ${pagination.current_page === pagination.last_page ? "opacity-50 cursor-not-allowed" : ""}">
            Next
        </button>
    `;

    container.innerHTML = buttons;
}

let selectedVendorId = null;

function selectVendorRow(row, id) {
    // uncheck all
    document.querySelectorAll(".vendor-checkbox").forEach((cb) => {
        cb.checked = false;
    });

    // check current
    const checkbox = row.querySelector(".vendor-checkbox");
    checkbox.checked = true;

    selectedVendorId = id;

    console.log("Selected Vendor ID:", selectedVendorId);
}
function fillEditVendorForm(response) {
    const vendor = response.data ? response.data : response;

    const form = document.getElementById("EditVendorForm");
    if (!form) {
        console.error("EditVendorForm not found");
        return;
    }

    const setValue = (id, value = "") => {
        const el = document.getElementById(id);
        if (!el) {
            console.error(`${id} not found`);
            return;
        }
        el.value = value ?? "";
    };

    setValue("edit_vendor_id", vendor.id);
    setValue("edit_code", vendor.code);
    setValue("edit_name", vendor.name);
    setValue("edit_contact_person", vendor.contact_person);
    setValue("edit_email", vendor.email);
    setValue("edit_phone1", vendor.phone1);
    setValue("edit_phone2", vendor.phone2);
    setValue("edit_country", vendor.country);
    setValue("edit_city", vendor.city);
    setValue("edit_website", vendor.website);
    setValue("edit_address1", vendor.address1);
    setValue("edit_address2", vendor.address2);

    const statusEl = document.getElementById("edit_status");
    if (statusEl) {
        statusEl.checked = Number(vendor.status) === 1;
    } else {
        console.error("edit_status not found");
    }

    console.log("Filled vendor form:", vendor);
}

document
    .getElementById("btnEditvendor")
    .addEventListener("click", openEditVendor);

async function openEditVendor() {
    if (!selectedVendorId) {
        showToast({
            message: "Please select a vendor first.",
            type: "error",
        });

        return;
    }

    try {
        const response = await fetch(`/vendors/${selectedVendorId}`, {
            method: "GET",
            headers: {
                Accept: "application/json",
            },
        });

        const vendor = await response.json();

        if (!response.ok) {
            showToast({
                message: vendor.message || "Vendor not found.",
                type: "error",
            });
            return;
        }
        console.log("Vendor data for editing:", vendor);
        // 🔥 Fill old data into form
        fillEditVendorForm(vendor);

        // or remove hidden manually if needed
    } catch (error) {
        console.error(error);

        showToast({
            message: "Server error.",
            type: "error",
        });
    }
}

async function updateVendor() {
    const form = document.getElementById("EditVendorForm");
    const vendorId = document.getElementById("edit_vendor_id").value;
    const btn = document.querySelector(
        '#EditVendorForm button[onclick="updateVendor()"]',
    );

    if (!vendorId) {
        showToast({
            message: "Vendor ID not found.",
            type: "error",
        });
        return;
    }

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = "Updating...";
    btn.classList.add("opacity-50", "cursor-not-allowed");

    const formData = new FormData(form);
    formData.append("_method", "PUT");

    try {
        const response = await fetch(`/vendors/${vendorId}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                    .value,
                Accept: "application/json",
            },
            body: formData,
        });

        const data = await response.json();

        if (response.ok) {
            showToast({
                message: "Vendor updated successfully!",
                type: "success",
            });

            loadVendors(1);

            // optional close modal
            // document.querySelector('[data-modal-hide="default-modal-edit-vendor"]').click();
        } else {
            showToast({
                message: data.message || "Failed to update vendor",
                type: "error",
            });
        }
    } catch (error) {
        console.error(error);
        showToast({
            message: "Server error",
            type: "error",
        });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
        btn.classList.remove("opacity-50", "cursor-not-allowed");
    }
}

async function FetchPurchase(page = 1) {
    const tbody = document.getElementById("PurchaseTableBody");

    try {
        const params = new URLSearchParams({
            page: page,
        });

        const res = await fetch(`/purchases/fetch?${params}`);

        const result = await res.json();

        tbody.innerHTML = "";

        if (!result.status) {
            showToast({
                message: result.message,
                type: "warning",
            });

            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-4 text-gray-500">
                        No Data
                    </td>
                </tr>
            `;
            return;
        }

        showToast({
            message: result.message,
            type: "success",
        });

        const rows = result.data.data;

        rows.forEach((purchase) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td class="px-4 py-3 border text-sm font-medium text-gray-800">
                    ${purchase.no ?? ""}
                </td>

                <td class="px-4 py-3 border text-sm text-gray-700">
                    ${purchase.posting_date ?? ""}
                </td>

                <td class="px-4 py-3 border text-sm">
                    <div class="font-medium text-gray-800">
                        ${purchase.vendor?.name ?? "General Vendor"}
                    </div>
                    <div class="text-xs text-gray-500">
                        ${purchase.vendor?.code ?? ""}
                    </div>
                </td>

                <td class="px-4 py-3 border text-sm text-gray-700">
                    ${purchase.vendor?.contact_person ?? ""}
                </td>
                <td class="px-4 py-3 border text-center text-sm">
                    ${purchase.lines?.length ?? 0}
                </td>
                <td class="px-4 py-3 border text-center text-sm">
                    ${purchase.lines?.length ?? 0}
                </td>

                <td class="px-4 py-3 border text-center text-sm">
                    ${
                        purchase.lines?.reduce((sum, line) => {
                            return sum + Number(line.quantity ?? 0);
                        }, 0) ?? 0
                    }
                </td>

                <td class="px-4 py-3 border text-right text-sm font-semibold text-green-600">
                    $
                    ${
                        purchase.lines
                            ?.reduce((sum, line) => {
                                return sum + Number(line.line_amount ?? 0);
                            }, 0)
                            .toFixed(2) ?? "0.00"
                    }
                </td>

                <td class="px-4 py-3 border text-sm text-gray-600">
                    ${purchase.created_by ?? "-"}
                </td>

                <td class="px-4 py-3 border text-center">
                    <div class="flex gap-2 justify-center">

                  
                    </div>
                </td>
                `;

            tbody.appendChild(tr);
        });
    } catch (error) {
        showToast({
            message: "Failed loading purchases",
            type: "danger",
        });

        console.error(error);
    }
}

window.addEventListener("DOMContentLoaded", async () => {
    let categories = [];

    try {
        const response = await fetch("/categories");
        categories = await response.json();
    } catch (error) {
        console.error("Failed to load categories:", error);
    }

    const categorySelect = document.getElementById("category_filter");

    if (!categorySelect) {
        console.error("category_filter not found");
        return;
    }

    categorySelect.innerHTML = "";

    // example row selector
    const row = document.querySelector("[data-category_id]");

    const currentCategoryId = row?.getAttribute("data-category_id") || "";

    const currentCategoryExists = categories.some(
        (cat) => String(cat.id) === currentCategoryId,
    );

    console.log(currentCategoryExists);
});
