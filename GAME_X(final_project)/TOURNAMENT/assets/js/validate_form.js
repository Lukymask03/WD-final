document.addEventListener("DOMContentLoaded", () => {
    // Select all forms with data-validate="true"
    const forms = document.querySelectorAll("form[data-validate='true']");

    forms.forEach(form => {
        form.addEventListener("submit", (e) => {
            let valid = true;

            // Check all required fields
            form.querySelectorAll("[required]").forEach(input => {
                if (input.value.trim() === "") {
                    alert(`Please fill out the ${input.name} field.`);
                    valid = false;
                }
            });

            // Validate email fields
            const emailField = form.querySelector("[type='email']");
            if (emailField) {
                const pattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
                if (!pattern.test(emailField.value.trim())) {
                    alert("Please enter a valid email address.");
                    valid = false;
                }
            }

            // Prevent submission if invalid
            if (!valid) e.preventDefault();
        });
    });
});
