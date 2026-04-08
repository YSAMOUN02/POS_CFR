
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
