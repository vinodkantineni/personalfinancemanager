document.addEventListener("DOMContentLoaded", () => {
    const uploadForm = document.getElementById("upload-form");
    const fileInput = document.getElementById("file-input");
    const message = document.getElementById("message");
    const fileList = document.getElementById("file-list");
    const chartContainer = document.createElement("div");
    chartContainer.id = "chart-container";
    document.querySelector("main").appendChild(chartContainer);

    // Load Chart.js and SheetJS
    const chartScript = document.createElement("script");
    chartScript.src = "https://cdn.jsdelivr.net/npm/chart.js";
    document.head.appendChild(chartScript);

    const sheetScript = document.createElement("script");
    sheetScript.src = "https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js";
    document.head.appendChild(sheetScript);

    function loadFiles() {
        fetch("upload.php", {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            fileList.innerHTML = "";
            message.textContent = "";
            if (data.error) {
                message.textContent = "Error fetching files: " + data.error;
                console.error("Server Error:", data.error);
                return;
            }
            if (data.length === 0) {
                message.textContent = "No files uploaded yet.";
                return;
            }
            data.forEach(file => {
                const li = document.createElement("li");
                li.textContent = file.file_name + " ";
                const viewBtn = document.createElement("button");
                viewBtn.textContent = "VIEW";
                viewBtn.onclick = () => {
                    window.open(`view_file.php?file=${encodeURIComponent(file.file_path)}`, "_blank");
                };
                li.appendChild(viewBtn);
                fileList.appendChild(li);
            });
            // Generate charts for the first file
            if (data.length > 0) {
                loadExcelData(data[0].file_path);
            }
        })
        .catch(error => {
            console.error("Fetch error:", error.message);
            message.textContent = "Error fetching uploaded files: " + error.message;
        });
    }

    function loadExcelData(filePath) {
        fetch(filePath)
        .then(response => response.arrayBuffer())
        .then(data => {
            const workbook = XLSX.read(data, { type: 'array' });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

            const headers = jsonData[0];
            const dateIdx = headers.indexOf("Date");
            const detailsIdx = headers.indexOf("Transaction Details");
            const categoryIdx = headers.indexOf("Category");
            const amountIdx = headers.indexOf("Amount (₹)");

            const transactions = jsonData.slice(1).map(row => ({
                date: row[dateIdx],
                details: row[detailsIdx],
                category: row[categoryIdx],
                amount: parseFloat(row[amountIdx]) || 0
            }));

            // Filter received and sent transactions
            const received = transactions.filter(t => t.amount > 0);
            const sent = transactions.filter(t => t.amount < 0).map(t => ({ ...t, amount: -t.amount }));

            // Data for charts
            const receivedBySender = {};
            const sentByPerson = {};
            const expenseByCategory = {};
            const flowByCategory = {};
            const transactionsByDate = {};

            received.forEach(t => {
                const sender = t.details.match(/Received from (.+)/)?.[1] || "Unknown";
                receivedBySender[sender] = (receivedBySender[sender] || 0) + t.amount;
            });

            sent.forEach(t => {
                const recipient = t.details.match(/to (.+)/)?.[1] || t.details || "Unknown";
                sentByPerson[recipient] = (sentByPerson[recipient] || 0) + t.amount;
            });

            transactions.forEach(t => {
                const cat = t.category || "Unknown";
                if (t.amount < 0) {
                    expenseByCategory[cat] = (expenseByCategory[cat] || 0) - t.amount;
                }
                flowByCategory[cat] = (flowByCategory[cat] || 0) + t.amount;
                const date = t.date.split(',')[0]; // Extract date part
                if (!transactionsByDate[date]) transactionsByDate[date] = { Bills: 0, Dining: 0, Groceries: 0, Income: 0, Transfer: 0 };
                if (t.amount < 0) {
                    if (cat.includes("Bills")) transactionsByDate[date].Bills++;
                    else if (cat.includes("Dining")) transactionsByDate[date].Dining++;
                    else if (cat.includes("Groceries")) transactionsByDate[date].Groceries++;
                    else if (cat.includes("Income")) transactionsByDate[date].Income++;
                    else if (cat.includes("Transfer")) transactionsByDate[date].Transfer++;
                }
            });

            // Create charts with darker, vibrant colors
            chartContainer.innerHTML = "";
            const createChart = (id, type, labels, datasets, title) => {
                const canvas = document.createElement("canvas");
                canvas.id = id;
                chartContainer.appendChild(canvas);
                new Chart(canvas.getContext('2d'), {
                    type: type,
                    data: { labels, datasets },
                    options: {
                        scales: { y: { beginAtZero: true, title: { display: true, text: 'Amount (₹)' } } },
                        plugins: { title: { display: true, text: title } }
                    }
                });
            };

            createChart("receivedChart", "bar", Object.keys(receivedBySender), [{
                label: 'Received',
                data: Object.values(receivedBySender),
                backgroundColor: '#2E86AB', // Dark teal
                borderColor: '#1B5E6F',
                borderWidth: 1
            }], "Total Money Received per Sender");

            createChart("sentChart", "bar", Object.keys(sentByPerson), [{
                label: 'Sent',
                data: Object.values(sentByPerson),
                backgroundColor: '#E63946', // Dark red
                borderColor: '#A4161A',
                borderWidth: 1
            }], "Total Money Sent per Person");

            createChart("expenseChart", "bar", Object.keys(expenseByCategory), [{
                label: 'Expense',
                data: Object.values(expenseByCategory),
                backgroundColor: '#4A69BD', // Dark blue
                borderColor: '#2D3E8C',
                borderWidth: 1
            }], "Total Expense by Category");

            createChart("flowChart", "bar", Object.keys(flowByCategory), [{
                label: 'Net Flow',
                data: Object.values(flowByCategory),
                backgroundColor: '#F4A261', // Dark orange
                borderColor: '#C85400',
                borderWidth: 1
            }], "Overall Money Flow by Category");

            createChart("transactionChart", "bar", Object.keys(transactionsByDate), [
                { label: 'Bills & Utilities', data: Object.values(transactionsByDate).map(d => d.Bills), backgroundColor: '#264653', borderColor: '#1A2F36', borderWidth: 1 },
                { label: 'Dining & Restaurants', data: Object.values(transactionsByDate).map(d => d.Dining), backgroundColor: '#E76F51', borderColor: '#B6442C', borderWidth: 1 },
                { label: 'Groceries & Essentials', data: Object.values(transactionsByDate).map(d => d.Groceries), backgroundColor: '#2A9D8F', borderColor: '#1D6A64', borderWidth: 1 },
                { label: 'Income & Credits', data: Object.values(transactionsByDate).map(d => d.Income), backgroundColor: '#E9C46A', borderColor: '#C99A2A', borderWidth: 1 },
                { label: 'Transfer & Payments', data: Object.values(transactionsByDate).map(d => d.Transfer), backgroundColor: '#8E5B3A', borderColor: '#6A3F27', borderWidth: 1 }
            ], "Number of Transactions Over Time");
        })
        .catch(error => console.error("Excel load error:", error));
    }

    uploadForm.addEventListener("submit", (e) => {
        e.preventDefault();
        if (fileInput.files.length === 0) {
            message.textContent = "Please select a file to upload.";
            return;
        }

        const formData = new FormData();
        formData.append("file", fileInput.files[0]);

        fetch("upload.php", {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            message.textContent = data.success || data.error;
            if (data.success) {
                fileInput.value = ""; // Clear file input
                loadFiles(); // Refresh file list and charts
                const li = document.createElement("li");
                li.textContent = fileInput.files[0].name + " ";
                const runBtn = document.createElement("button");
                runBtn.textContent = "RUN";
                runBtn.onclick = () => {
                    runPythonScript("Uploads/" + fileInput.files[0].name);
                };
                li.appendChild(runBtn);
                fileList.appendChild(li);
            }
        })
        .catch(error => {
            console.error("Upload failed:", error.message);
            message.textContent = "Error uploading file: " + error.message;
        });
    });

    function runPythonScript(filePath) {
        fetch("run_script.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ file_path: filePath })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.location.href = "output.html";
            } else {
                alert("Error running script: " + data.details);
            }
        })
        .catch(error => {
            console.error("Run error:", error.message);
            alert("Failed to run script: " + error.message);
        });
    }

    loadFiles();
});