const input_document_date_value = document.getElementById("document_dateInput");
function print_document(document_type) {
    const document_date_value = input_document_date_value.value;
    const document_date = new Date(document_date_value);
    const options = { day: "2-digit", month: "short", year: "numeric" };
    const formattedDocumentDate = document_date.toLocaleDateString(
        "en-GB",
        options,
    );

    // docutment Header
    const document_header = document.getElementById("document-header");
    // Title
    let document_title = document.getElementById("document_title");
    document_title.querySelector("h1").textContent = document_type;
    let logo = document.getElementById("logo");
    const logo_80mm = document.getElementById("logo_80mm").innerHTML;
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

    const pos_info = {
        company: "ឈូកមាស ផ្គត់ផ្គង់សាច់គ្រប់ប្រភេទ",
        description:
            "មានលក់ដុំនិងរាយ មាន់ ទា ជើងមាន់ ស្លាបមាន់ សាច់ទ្រូងមាន់ និងគ្រឿងប្រឡាក់សាច់",
        address1: "ភ្នំពេញ",
        address2: "",
        phone: "011 79 80 87 / 097 779 80 87",
        email: "",
        telegram: "016 79 80 87",
        seller: "016 79 80 87",
        name: "អតិថិជនទូទៅ",
    };
    //  paymentData = {
    //             paymentMethod: Payment_Method,
    //             document_date: document_date ?? null,
    //             customer_type: customer_type ?? null,
    //             customer_id: customer_id ?? null,
    //             customer_name: customer_name ?? null,
    //             customer_phone: customer_phone ?? null,
    //             customer_address: customer_address ?? null,
    //             customer_deposit: customer_deposit ?? null,
    //             remark: remark ?? null,
    //         };
    // Open new window
    const printWindow = window.open("", "_blank", "width=800,height=600");

    if (document_type === "Invoice") {
        printWindow.document.write(`
                <html>
                <head>
                    <title>Invoice</title>
                    ${style_invoice}
                </head>
                <body onload="window.print(); window.close();">

                    <div class="header-center">
                        <h2>${pos_info.company}</h2>
                        <div>${pos_info.description}</div>

                    </div>

                    <div class="line"></div>
                    <div class="invoice-title">វិក្កយបត្រ</div>
                    <div class="line"></div>
                    <div class="info-section">
                        <div class="info-box">
                            <div><span class="label">ឈ្មោះអតិថិជន:</span> <span>${paymentData.customer_name ?? ""}</span></div>
                            <div><span class="label">លេខទូរស័ព្ទ:</span> <span>${paymentData.customer_phone ?? ""}</span></div>
                            <div><span class="label">អាសយដ្ឋាន:</span> <span>${paymentData.customer_address ?? ""}</span></div>
                        </div>

                        <div class="info-box right-box">
                            <div><span class="label">លេខវិក្កយបត្រ:</span> <span>${invoice_no}</span></div>
                            <div><span class="label">ថ្ងៃទី:</span> <span>${formattedDocumentDate}</span></div>
                        </div>
                    </div>

                    ${table_data.innerHTML}

                    <div class="signature-section">
                        <div class="signature-box">
                            ------------------<br>
                            ហត្ថលេខា<br>
                            អ្នកទិញ
                        </div>

                        <div class="signature-box">
                            ------------------<br>
                            ហត្ថលេខា<br>
                            អ្នកលក់
                        </div>
                    </div>

                </body>
                </html>
    `);
    } else if (document_type === "Receipt") {
        table_footer_description.innerHTML = `
                    <div class="font-mid" style="line-height:1.5;">
                        <div style="font-weight:bold; text-decoration:underline; margin-bottom:6px;">
                            <center>Thanks for you! Please come again.</center>
                        </div>


                    </div>
                `;

        printWindow.document.write(`
                <html>
                <head>
                    <title>Receipt</title>
                    <style>

                        @page {
                            size: 80mm auto;
                            margin: 0 !important;
                        }

                        * {
                            margin: 0 !important;
                            padding: 0 !important;
                            box-sizing: border-box;
                            font-family: 'Noto Serif Khmer', serif;
                        }
                        /* Khmer */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/khmer.woff2') format('woff2');
                            unicode-range: U+1780-17FF, U+19E0-19FF, U+200C-200D, U+25CC;
                        }

                        /* Latin Extended */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/latinex.woff2') format('woff2');
                            unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
                        }

                        /* Latin */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/latin.woff2') format('woff2');
                            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
                        }

                        html, body {
                            width: 80mm !important;
                            max-width: 80mm !important;
                            font-family: 'Noto Serif Khmer', serif;
                            font-size:11px;
                            color: black !important;
                            font-weight: bold;


                        }
                         img {
                              image-rendering: pixelated; /* tries to make logos sharper */
                            }
                        body {
                            padding: 3mm !important; /* tiny inner safe padding */
                        }

                        table {
                            width: 100% !important;
                            border-collapse: collapse;
                            margin: 8px 0 !important;
                            border: 1px solid #000;
                        }
                        thead tr{
                        background-color: black !important;
                        color:white !important;
                        }
                        table th:nth-child(6), table td:nth-child(6) {
                         display: none;
                         }

                        th, td {
                         border: 1px solid #00000050;
                            padding: 1px 2px !important;
                            font-size: 10px;
                            font-weight: bold;
                            color: black !important;
                        }
                        .font-mid{
                            font-size: 11px;
                            color: black !important
                        }
                        </style>
                </head>
                <body onload="window.print(); window.close();">

                    <!-- Header -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        ${logo_80mm}
                        <div style="font-size:12px; font-weight:bold;">

                        </div>
                    </div>

                 <!-- Seller + Date in 2-column grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0 ;">

                            <!-- Left column: Shop info -->
                            <div  class="font-mid"  style="display: grid; gap:3px; text-align: left;">

                            </div>

                          <!-- Right column: Dates / Invoice (2-grid, all right aligned) -->
                            <div class="font-mid" style="
                                display: grid;
                                grid-template-columns: max-content max-content;

                                justify-content: end;
                                text-align: right;
                            ">
                                <div><b>Date:</b></div>
                                <div>&ensp; ${formattedDocumentDate}</div>

                                <div><b>Reciept No:</b></div>
                                <div>
                                 &ensp; ${reciept_no}

                                </div>

                            </div>
                        </div>
                       <div style="font-size:10px; font-weight:bold; margin-bottom:10px;"> <center>${document_title.innerHTML}</center></div>
                    <!-- Table -->
                    ${table_data.innerHTML}
                    <div class="font-mid mt-2">${table_footer.innerHTML} </div>


                </body>
                </html>
                `);
    } else if (document_type === "Order") {
        let formattedOrderNo = String(document_no).padStart(3, "0");
        table_footer_description.innerHTML = `
                    <div class="font-mid" style="line-height:1.5;">
                        <div style="font-weight:bold; text-decoration:underline; margin-bottom:6px;">
                            <center>Thanks for your Order.</center>
                        </div>


                    </div>
                `;

        printWindow.document.write(`
                <html>
                <head>
                    <title>Order</title>
                  <style>

                        @page {
                            size: 80mm auto;
                            margin: 0 !important;
                        }

                        * {
                            margin: 0 !important;
                            padding: 0 !important;
                            box-sizing: border-box;
                            font-family: 'Noto Serif Khmer', serif;
                        }
                        /* Khmer */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/khmer.woff2') format('woff2');
                            unicode-range: U+1780-17FF, U+19E0-19FF, U+200C-200D, U+25CC;
                        }

                        /* Latin Extended */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/latinex.woff2') format('woff2');
                            unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
                        }

                        /* Latin */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/latin.woff2') format('woff2');
                            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
                        }


                        html, body {
                            width: 80mm !important;
                            max-width: 80mm !important;
                            font-family: 'Noto Serif Khmer', serif;
                            font-size:10px;
                            color: black !important;
                            font-weight: bold;


                        }
                         img {
                              image-rendering: pixelated; /* tries to make logos sharper */
                            }
                        body {
                            padding: 3mm !important; /* tiny inner safe padding */
                        }

                        table {
                            width: 100% !important;
                            border-collapse: collapse;
                            margin: 8px 0 !important;
                            border: 1px solid #000;
                        }

                        table th:nth-child(6), table td:nth-child(6) {
                         display: none;
                         }

                        th, td {
                            border: 1px solid black;
                            padding: 1px 2px !important;
                            font-size: 10px;
                            font-weight: bold;
                            color: black !important;
                        }
                        .font-mid{
                            font-size: 10px;
                            color: black !important
                        }
                        </style>
                </head>
                <body onload="window.print(); window.close();">

                    <!-- Header -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        ${logo_80mm}
                        <div style="font-size:12px; font-weight:bold;">
                            ${document_title.innerHTML}
                        </div>
                    </div>

                 <!-- Seller + Date in 2-column grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">

                            <!-- Left column: Shop info -->
                            <div  class="font-mid"  style="display: grid; gap:3px; text-align: left;">

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

                                <div><b>QUEUE No:</b></div>
                                <div>

                                    ORDER-${formattedOrderNo}
                                </div>

                            </div>
                        </div>
                    <!-- Table -->
                    ${table_data.innerHTML}
                    <div class="font-mid">${table_footer.innerHTML} </div>


                </body>
                </html>
                `);
    } else if (document_type === "Quotation") {
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
                                /* Khmer */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/khmer.woff2') format('woff2');
                            unicode-range: U+1780-17FF, U+19E0-19FF, U+200C-200D, U+25CC;
                        }

                        /* Latin Extended */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/latinex.woff2') format('woff2');
                            unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
                        }

                        /* Latin */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/latin.woff2') format('woff2');
                            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
                        }

                        html, body {

                            font-family: 'Noto Serif Khmer', serif;
                             font-size:10px;
                            color: black !important;
                        }
                        table {
                        width: 100%;
                         border-collapse: collapse;
                          margin: 10px 0;
                           }
                        th, td {
                            border: 1px solid #000;
                            padding: 6px;
                            text-align: left;
                            font-size:10px;
                            color: black !important;
                          }
                        th {
                        background-color: #f0f0f0;
                         }
                        .invoice-header h2 { margin: 0; }
                        .font-mid{
                            font-size:10px;
                             color: black !important;
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
                                <strong>Quotation for:</strong>
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

                                <div><b>Quotation:</b></div>
                                <div>
                                    &ensp;${quotation_no}
                                </div>

                            </div>
                        </div>
                    <!-- Table -->
                    ${table_data.innerHTML}
                    <div class="font-mid">${table_footer.innerHTML} </div>


                </body>
                </html>
                `);
    } else if (document_type === "Delivery Note") {
        let today = new Date().toLocaleDateString(); // e.g., "23/02/2026"

        let footer_panha_delivery_note = `


                <!-- Seller -->
                <div style="width:50%; display:flex; flex-direction:column;">
                    <div style="font-weight:bold;">Seller</div>
                    <div style="margin-top:10px;">
                        Name: <span style="display:inline-block; width:150px; border-bottom:1px solid #000;"></span>
                    </div>
                    <div style="margin-top:5px;">
                        Date: <span style="display:inline-block; width:150px; border-bottom:1px solid #000;">${today}</span>
                    </div>
                </div>

                <!-- Receiver -->
                <div style="width:50%; display:flex; justify-content:flex-end; flex-direction:column;">
                    <div style="font-weight:bold;">Receiver</div>
                    <div style="margin-top:10px;">
                        Name: <span style="display:inline-block; width:150px; border-bottom:1px solid #000;"></span>
                    </div>
                    <div style="margin-top:5px;">
                        Date: <span style="display:inline-block; width:150px; border-bottom:1px solid #000;">${today}</span>
                    </div>
                </div>


            `;
        let page_A4_style = `
        <style>
                        body {
                            font-family: 'Noto Serif Khmer', serif;
                            position: relative;

                            background-color: white;
                          }
  /* Khmer */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/khmer.woff2') format('woff2');
                            unicode-range: U+1780-17FF, U+19E0-19FF, U+200C-200D, U+25CC;
                        }

                        /* Latin Extended */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/latinex.woff2') format('woff2');
                            unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
                        }

                        /* Latin */
                        @font-face {
                            font-family: 'Noto Serif Khmer';
                            font-style: normal;
                            font-weight: 400;
                            font-stretch: 100%;
                            font-display: swap;
                            src: url('/assets/Font/latin.woff2') format('woff2');
                            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
                        }

                        html, body {

                            font-family: 'Noto Serif Khmer', serif;


                        }

                        table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                        }

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
                        .footer {

                            position: absolute;
                            bottom:0;
                            width: 100%;
                            display: flex;
                            justify-content: space-between;


                        }
                        .total_print{
                        display:none;}
                          #currency_exchange{
                            display:none;}
                        @media print {
                            button { display: none; }
                        }
                    </style>
        `;
        let footer_delivery_note = footer_panha_delivery_note;
        let page_style = page_A4_style;
        printWindow.document.write(`
                <html>
                <head>
                    <title>Invoice</title>
                    ${page_style}
                </head>
                <body onload="window.print(); window.close();">

                    <!-- Header -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        ${logo.innerHTML}

                    </div>

                 <!-- Seller + Date in 2-column grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 5px;">

                            <!-- Left column: Shop info -->
                            <div class="font-mid" style="display: grid; gap:3px; text-align: left;">

                                <div style="font-size:15px; margin: 0px 5px;  font-weight:bold;">
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

                                <div><b>Delivery No:</b></div>
                                <div>
                                  ${delivery_note_no}
                                </div>

                            </div>


                        </div>

                    <!-- Table -->
                    ${table_data.innerHTML}
                  <div class="footer">
                    ${footer_delivery_note}
                    </div>

                </body>
                </html>
                `);
    }

    printWindow.document.close();
}

let style_invoice = `
<style>
@font-face{
    font-family:'Noto Serif Khmer';
    src:url('/assets/Font/khmer.woff2') format('woff2');
    unicode-range:U+1780-17FF,U+19E0-19FF,U+200C-200D,U+25CC;
}
@font-face{
    font-family:'Noto Serif Khmer';
    src:url('/assets/Font/latinex.woff2') format('woff2');
    unicode-range:U+0100-02BA,U+1E00-1EFF,U+2020,U+20A0-20AB;
}

body{
    font-family:'Noto Serif Khmer',serif;
    margin:15px;
    color:#000;
    font-size:13px;
}
.header-center{text-align:center;line-height:1.6}
.line{border-top:2px solid #000;margin:10px 0}
.invoice-title{text-align:center;font-size:24px;font-weight:bold}
.info-section{
    display:flex;
    justify-content:space-between;
    margin:15px 0;
    gap:20px;
}
.info-box{
    width:48%;
}
.info-box div{
    display:flex;
    margin-bottom:6px;
}
.label{
    min-width:120px;
    font-weight:bold;
}
.right-box .label{
    min-width:100px;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:8px;
}
th,td{
    border:1px solid #000;
    padding:5px;
    text-align:center;
    font-size:12px;
}
.summary td{
    text-align:right;
    font-weight:bold;
}
.signature-section{
    display:flex;
    justify-content:space-between;
    margin-top:60px;
    text-align:center;
}
.signature-box{width:180px}

@media print{
    button{display:none}
}
</style>
`;
