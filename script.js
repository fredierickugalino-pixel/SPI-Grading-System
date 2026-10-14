document.querySelectorAll(".show-password").forEach(function (button) {
    button.addEventListener("click", function () {
        const input = document.getElementById(button.dataset.target);

        if (!input) {
            return;
        }

        const shouldShowPassword = input.type === "password";
        input.type = shouldShowPassword ? "text" : "password";
        button.textContent = shouldShowPassword ? "Hide" : "Show";
    });
});

const registrationForm = document.getElementById("registration-form");

if (registrationForm) {
    const password = document.getElementById("password");
    const confirmation = document.getElementById("confirm_password");
    const email = document.getElementById("email");
    const emailStatus = document.getElementById("email-status");
    const confirmationStatus = document.getElementById("confirm-status");
    let emailCheckTimer;
    let checkedEmail = "";
    let emailCheckInProgress = false;
    let isSubmitting = false;
    const passwordRules = {
        length: value => value.length >= 8,
        lowercase: value => /[a-z]/.test(value),
        uppercase: value => /[A-Z]/.test(value),
        special: value => /[^a-zA-Z0-9]/.test(value)
    };

    function updatePasswordChecks() {
        const value = password.value;
        Object.entries(passwordRules).forEach(([rule, passes]) => {
            const item = document.querySelector(`[data-rule="${rule}"]`);
            if (item) {
                item.classList.toggle("is-met", passes(value));
            }
        });
    }

    function updateConfirmation() {
        const mismatch = confirmation.value !== "" && confirmation.value !== password.value;
        confirmation.setCustomValidity(mismatch ? "Passwords do not match." : "");
        confirmationStatus.textContent = mismatch ? "Passwords do not match." : "";
        confirmationStatus.classList.toggle("is-error", mismatch);
    }

    password.addEventListener("input", function () {
        updatePasswordChecks();
        updateConfirmation();
    });
    confirmation.addEventListener("input", updateConfirmation);

    function setEmailStatus(message, state) {
        emailStatus.textContent = message;
        emailStatus.classList.toggle("is-error", state === "error");
        emailStatus.classList.toggle("is-success", state === "success");
        emailStatus.classList.toggle("is-pending", state === "pending");
    }

    async function checkEmailAvailability() {
        const value = email.value.trim();
        if (!email.validity.valid || value === "") {
            checkedEmail = "";
            setEmailStatus("", "");
            return false;
        }

        emailCheckInProgress = true;
        setEmailStatus("Checking email…", "pending");
        try {
            const response = await fetch("php/check_email.php?email=" + encodeURIComponent(value), {
                headers: { "Accept": "application/json" },
                cache: "no-store"
            });
            const result = await response.json();
            if (!response.ok || typeof result.available !== "boolean") {
                throw new Error("Email check failed");
            }
            if (email.value.trim().toLowerCase() !== value.toLowerCase()) {
                return false;
            }
            checkedEmail = value.toLowerCase();
            if (result.available) {
                setEmailStatus("This email is available.", "success");
                return true;
            }
            setEmailStatus("This email is already registered. Use another email or log in.", "error");
            return false;
        } catch (error) {
            checkedEmail = "";
            setEmailStatus("Could not check this email. Please try again.", "error");
            return false;
        } finally {
            emailCheckInProgress = false;
        }
    }

    email.addEventListener("input", function () {
        checkedEmail = "";
        clearTimeout(emailCheckTimer);
        setEmailStatus("", "");
        if (email.validity.valid && email.value.trim()) {
            emailCheckTimer = setTimeout(checkEmailAvailability, 450);
        }
    });
    email.addEventListener("blur", function () {
        clearTimeout(emailCheckTimer);
        checkEmailAvailability();
    });

    registrationForm.addEventListener("submit", async function (event) {
        if (isSubmitting) return;
        event.preventDefault();
        updateConfirmation();
        if (!registrationForm.checkValidity()) {
            registrationForm.reportValidity();
            return;
        }
        clearTimeout(emailCheckTimer);
        const normalizedEmail = email.value.trim().toLowerCase();
        if (emailCheckInProgress || checkedEmail !== normalizedEmail) {
            const available = await checkEmailAvailability();
            if (!available) return;
        }
        updateConfirmation();
        if (!registrationForm.checkValidity()) {
            registrationForm.reportValidity();
            return;
        }
        if (checkedEmail !== email.value.trim().toLowerCase()) {
            setEmailStatus("Email changed. Check its availability again.", "error");
            return;
        }
        isSubmitting = true;
        HTMLFormElement.prototype.submit.call(registrationForm);
    });
}

const resetPasswordForm = document.querySelector("form[action='reset_password.php']");

if (resetPasswordForm) {
    const password = resetPasswordForm.querySelector("#password");
    const confirmation = resetPasswordForm.querySelector("#confirm_password");
    const confirmationStatus = resetPasswordForm.querySelector("#confirm-status");
    const rules = {
        length: value => value.length >= 8,
        lowercase: value => /[a-z]/.test(value),
        uppercase: value => /[A-Z]/.test(value),
        special: value => /[^a-zA-Z0-9]/.test(value)
    };

    function updateResetPasswordChecks() {
        Object.entries(rules).forEach(([rule, passes]) => {
            const item = resetPasswordForm.querySelector(`[data-rule="${rule}"]`);
            if (item) item.classList.toggle("is-met", passes(password.value));
        });
    }

    function checkResetPasswordMatch() {
        const mismatch = confirmation.value !== "" && confirmation.value !== password.value;
        confirmation.setCustomValidity(mismatch ? "Passwords do not match." : "");
        confirmationStatus.textContent = mismatch ? "Passwords do not match." : "";
        confirmationStatus.classList.toggle("is-error", mismatch);
    }

    password.addEventListener("input", function () {
        updateResetPasswordChecks();
        checkResetPasswordMatch();
    });
    confirmation.addEventListener("input", checkResetPasswordMatch);
}
