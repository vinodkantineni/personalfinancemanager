document.addEventListener("DOMContentLoaded", () => {
    const uploadForm = document.getElementById("upload-form");
    const fileInput = document.getElementById("file-input");
    const message = document.getElementById("message");
    const fileList = document.getElementById("file-list");

    function loadFiles() {
        fetch("upload.php")
            .then(response => response.json())
            .then(data => {
                fileList.innerHTML = "";

                if (data.error) {
                    message.textContent = "Error fetching files: " + data.error;
                    console.error("Fetch Error:", data.error);
                    return;
                }

                if (data.length === 0) {
                    message.textContent = "No files uploaded yet.";
                    return;
                }

                data.forEach(file => {
                    const li = document.createElement("li");
                    li.textContent = file.file_name + " ";

                    // View Button
                    const viewBtn = document.createElement("button");
                    viewBtn.textContent = "VIEW";
                    viewBtn.onclick = () => {
                        window.open(`view_file.php?file=${encodeURIComponent(file.file_path)}`, "_blank");
                    };

                    // Run Button
                    const runBtn = document.createElement("button");
                    runBtn.textContent = "RUN";
                    runBtn.onclick = () => {
                        runPythonScript(file.file_path);
                    };

                    li.appendChild(viewBtn);
                    li.appendChild(runBtn);
                    fileList.appendChild(li);
                });
            })
            .catch(error => {
                console.error("Fetch error:", error);
                message.textContent = "Error fetching uploaded files.";
            });
    }

    function runPythonScript(filePath) {
        fetch("run_script.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ file_path: filePath })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "output.html"; // Redirect to output page
            } else {
                alert("Error running script: " + data.details);
            }
        })
        .catch(error => {
            console.error("Run error:", error);
            alert("Failed to run script.");
        });
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
        .then(response => response.json())
        .then(data => {
            message.textContent = data.success || data.error;
            if (data.success) {
                loadFiles();
            }
        })
        .catch(error => {
            console.error("Upload failed:", error);
            message.textContent = "Error uploading file.";
        });
    });

    loadFiles();
});
