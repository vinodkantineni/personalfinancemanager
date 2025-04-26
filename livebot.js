document.addEventListener("DOMContentLoaded", function () {
    const chatbotHeader = document.getElementById("chatbot-header");
    const chatbotBody = document.getElementById("chatbot-body");
    const chatbotMessages = document.getElementById("chatbot-messages");
    const chatbotInput = document.getElementById("chatbot-input");
    const chatbotSend = document.getElementById("chatbot-send");
    const chatbotSections = document.createElement("div");
    chatbotSections.id = "chatbot-sections";
    chatbotBody.insertBefore(chatbotSections, chatbotMessages);

    const sections = {
        "General Questions": [
            "How do I set up my account?",
            "Is my financial data secure?",
            "How do I reset my password?"
        ],
        "Expense Tracking & Budgeting": [
            "How do I add an expense or income?",
            "Can I categorize my expenses?",
            "How do I set a monthly budget?",
            "Can I track expenses across multiple accounts?",
            "Can I set alerts for overspending?",
            "How do I edit or delete an entry?"
        ],
        "Savings & Investment Tracking": [
            "How can I track my savings goals?",
            "What reports or graphs are available?"
        ],
        "AI-Based Financial Suggestions": [
            "How does the app suggest ways to save money?",
            "Can this app help me optimize my spending?"
        ],
        "Customization & Notifications": [
            "Can I customize spending categories?",
            "How do I set reminders for bill payments?",
            "Can I receive weekly/monthly financial summaries?"
        ],
        "Technical Support": [
            "My data is not updating, what should I do?",
            "Can I export my financial data?",
            "How do I delete my account permanently?"
        ]
    };

    function displaySections() {
        chatbotMessages.innerHTML = "";
        chatbotSections.innerHTML = "";
        for (let section in sections) {
            let btn = document.createElement("button");
            btn.textContent = section;
            btn.onclick = () => displayQuestions(section);
            chatbotSections.appendChild(btn);
        }
    }

    function displayQuestions(section) {
        chatbotMessages.innerHTML = "";
        chatbotSections.innerHTML = "";
        sections[section].forEach(question => {
            let btn = document.createElement("button");
            btn.textContent = question;
            btn.onclick = () => displayAnswer(question);
            chatbotSections.appendChild(btn);
        });
        let backBtn = document.createElement("button");
        backBtn.textContent = "Back";
        backBtn.onclick = displaySections;
        chatbotSections.appendChild(backBtn);
    }

    function displayAnswer(question) {
        chatbotMessages.innerHTML = "";
        chatbotSections.innerHTML = "";
        let answerDiv = document.createElement("div");
        answerDiv.className = "chatbot-message bot-message";
        answerDiv.textContent = chatbotResponses[question] || "Sorry, I don't have an answer for that yet.";
        chatbotMessages.appendChild(answerDiv);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        let backBtn = document.createElement("button");
        backBtn.textContent = "Back";
        backBtn.onclick = () => displayQuestions(Object.keys(sections).find(sec => sections[sec].includes(question)));
        chatbotSections.appendChild(backBtn);
    }

    chatbotHeader.addEventListener("click", () => {
        chatbotBody.style.display = chatbotBody.style.display === "block" ? "none" : "block";
        displaySections();
    });

    const chatbotResponses = {
        "How do I set up my account?": "To set up your account, click on 'Sign Up,' enter your details, and verify your email.",
        "Is my financial data secure?": "Yes! We use industry-standard encryption and security measures to keep your financial data safe.",
        "How do I reset my password?": "Click on 'Forgot Password' on the login page, enter your registered email, and follow the instructions.",
        "How do I add an expense or income?": "Go to the 'Transactions' section, click on 'Add Expense' or 'Add Income,' enter the details, and save it.",
        "Can I categorize my expenses?": "Yes! When adding an expense, you can assign it to a category like 'Food,' 'Rent,' or 'Entertainment.'",
        "How do I set a monthly budget?": "Go to 'Budget Settings,' choose a category or overall limit, enter your budget amount, and save.",
        "Can I track expenses across multiple accounts?": "Yes! You can add multiple bank accounts, credit cards, or wallets.",
        "Can I set alerts for overspending?": "Yes! In the 'Notifications' settings, enable 'Spending Alerts.'",
        "How do I edit or delete an entry?": "Go to 'Transactions,' find the entry you want to edit or delete, and confirm your action.",
        "How can I track my savings goals?": "Go to the 'Savings Goals' section, enter your target amount, and timeline.",
        "What reports or graphs are available?": "You can view spending breakdowns, income vs. expenses, and savings progress in the 'Reports' section.",
        "How does the app suggest ways to save money?": "Our AI analyzes your spending patterns and suggests ways to cut unnecessary expenses.",
        "Can this app help me optimize my spending?": "Yes! The app provides insights into spending habits and offers suggestions to improve your financial health.",
        "Can I customize spending categories?": "Yes! Go to 'Settings' > 'Categories' and create or modify categories.",
        "How do I set reminders for bill payments?": "In 'Reminders,' add a new bill, enter the due date, and enable notifications.",
        "Can I receive weekly/monthly financial summaries?": "Yes! Enable financial summary notifications in 'Settings.'",
        "My data is not updating, what should I do?": "Try refreshing the app or contact support if the issue persists.",
        "Can I export my financial data?": "Yes! Go to 'Settings' > 'Export Data' and choose your preferred format.",
        "How do I delete my account permanently?": "Go to 'Settings' > 'Account' > 'Delete Account.' This action is irreversible."
    };
});