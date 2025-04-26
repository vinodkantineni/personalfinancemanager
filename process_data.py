import os
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
import sys

# Ensure required arguments are passed
if len(sys.argv) < 2:
    print("Error: No file path provided. Please provide an Excel file path.")
    sys.exit(1)

file_path = sys.argv[1]

# Create output directory if it doesn't exist
output_dir = "uploads"
os.makedirs(output_dir, exist_ok=True)

# Load the Excel file
try:
    df = pd.read_excel(file_path)
except Exception as e:
    print(f"Error reading file: {e}")
    sys.exit(1)

# Convert 'Date' column to datetime format
if "Date" in df.columns:
    df["Date"] = pd.to_datetime(df["Date"], errors='coerce')
    df = df.dropna(subset=["Date"])
else:
    print("Error: 'Date' column not found.")
    sys.exit(1)

# Ensure required columns exist
required_columns = ["Category", "Amount (₹)", "Transaction Details"]
for col in required_columns:
    if col not in df.columns:
        print(f"Error: Required column '{col}' not found.")
        sys.exit(1)

# Fix Seaborn warning
sns.set_theme(style="whitegrid")

# --- 📊 1. Plot Expense by Category ---
category_expense = df.groupby("Category")["Amount (₹)"].sum().abs().sort_values()
category_plot_path = os.path.join(output_dir, "category_expense_plot.png")
if not category_expense.empty:
    plt.figure(figsize=(10, 5))
    sns.barplot(x=category_expense.values, y=category_expense.index, palette="viridis")
    plt.title("Total Expense by Category")
    plt.xlabel("Amount (₹)")
    plt.ylabel("Category")
    plt.savefig(category_plot_path)
    plt.close()

# --- 📊 2. Plot Money Received per Sender ---
df_credit = df[df["Amount (₹)"] > 0]
df_credit["Received From"] = df_credit["Transaction Details"].str.extract(r"from ([\w\s]+)")
received_amount = df_credit.groupby("Received From")["Amount (₹)"].sum().sort_values()
received_plot_path = os.path.join(output_dir, "received_amount_plot.png")
if not received_amount.empty:
    plt.figure(figsize=(10, 5))
    sns.barplot(x=received_amount.values, y=received_amount.index, palette="coolwarm")
    plt.title("Total Money Received per Sender")
    plt.xlabel("Amount (₹)")
    plt.ylabel("Sender")
    plt.savefig(received_plot_path)
    plt.close()

# --- 📊 3. Extract 'Sent To' values from Transaction Details ---
df["Sent To"] = df["Transaction Details"].str.extract(r"to ([\w\s]+)")
df_debit = df.dropna(subset=["Sent To"])
sent_plot_path = os.path.join(output_dir, "sent_amount_plot.png")

if df_debit.empty:
    df_debit = df[df["Amount (₹)"] < 0]
    df_debit["Sent To"] = "Unknown"

sent_amount = df_debit.groupby("Sent To")["Amount (₹)"].sum().abs().sort_values()
if not sent_amount.empty:
    plt.figure(figsize=(10, 5))
    sns.barplot(x=sent_amount.values, y=sent_amount.index, palette="magma")
    plt.title("Total Money Sent per Person")
    plt.xlabel("Total Amount Sent (₹)")
    plt.ylabel("Recipient")
    plt.savefig(sent_plot_path)
    plt.close()

# --- 📊 4. Plot Overall Money Flow (Category-based) ---
category_balance = df.groupby("Category")["Amount (₹)"].sum().sort_values()
category_balance_plot_path = os.path.join(output_dir, "category_balance_plot.png")
if not category_balance.empty:
    plt.figure(figsize=(10, 5))
    sns.barplot(x=category_balance.values, y=category_balance.index, palette="Set2")
    plt.title("Overall Money Flow by Category")
    plt.xlabel("Net Amount (₹)")
    plt.ylabel("Category")
    plt.savefig(category_balance_plot_path)
    plt.close()

# --- 📝 Generate HTML Report ---
html_content = f"""
<!DOCTYPE html>
<html>
<head>
    <title>Financial Report</title>
    <style>
        body {{ font-family: Arial, sans-serif; margin: 20px; }}
        h2 {{ color: #333; }}
        table {{ width: 100%; border-collapse: collapse; margin-bottom: 20px; }}
        th, td {{ border: 1px solid #ddd; padding: 8px; text-align: left; }}
        th {{ background-color: #f2f2f2; }}
        img {{ max-width: 80%; height: auto; margin-bottom: 20px; }}
    </style>
</head>
<body>

    <h1>Financial Report</h1>
    
    <h2>1. Total Expense by Category</h2>
    <img src="{category_plot_path}" alt="Category Expense">

    <h2>2. Total Money Received per Sender</h2>
    <img src="{received_plot_path}" alt="Money Received per Sender">

    <h2>3. Total Money Sent per Person</h2>
    <img src="{sent_plot_path}" alt="Money Sent per Person">

    <h2>4. Overall Money Flow by Category</h2>
    <img src="{category_balance_plot_path}" alt="Overall Money Flow">

    <h2>5. Sent Transactions Summary</h2>
    {sent_amount.to_frame().to_html()}

    <h2>6. Received Transactions Summary</h2>
    {received_amount.to_frame().to_html()}

</body>
</html>
"""

# Save HTML file
html_output_path = os.path.join(output_dir, "output.html")
with open(html_output_path, "w", encoding="utf-8") as f:
    f.write(html_content)

print(f"Report generated: {html_output_path}")
