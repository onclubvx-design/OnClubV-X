// ======================
// VARIABLES GLOBALES
// ======================
const modalOverlay = document.getElementById("modalOverlay");
const openModalBtn = document.getElementById("openModalBtn"); // Botón "Acceder"
const closeModalBtn = document.querySelector(".close-btn");
const tabButtons = document.querySelectorAll(".tab-btn");
const tabPanes = document.querySelectorAll(".tab-pane");
const registrationForm = document.getElementById("registrationForm");
const loginForm = document.getElementById("loginForm");
const successAlert = document.getElementById("successAlert");
const welcomeScreen = document.getElementById("welcomeScreen");
const openLoginBtn = document.getElementById("openLoginBtn"); // Botón "Acceso Administrativo"

// ======================
// GESTIÓN DEL MODAL
// ======================
class ModalManager {
    static open(defaultTab = "register") {
        if (modalOverlay) {
            modalOverlay.classList.add("show");
            document.body.style.overflow = "hidden";

            if (defaultTab === "login") {
                TabManager.activateTab("loginTab", "loginPane");
            } else if (defaultTab === "recover") {
                TabManager.activateTab(null, "recoverPane");
            } else {
                TabManager.activateTab("registerTab", "registerPane");
            }
        }
    }

    static close() {
        if (modalOverlay) {
            modalOverlay.classList.remove("show");
            document.body.style.overflow = "";
        }
    }

    static init() {
        if (openModalBtn) {
            openModalBtn.addEventListener("click", () => {
                this.open("register");
            });
        }

        if (openLoginBtn) {
            openLoginBtn.addEventListener("click", () => {
                this.open("login");
            });
        }

        if (modalOverlay) {
            modalOverlay.addEventListener("click", (e) => {
                if (e.target === modalOverlay) {
                    this.close();
                }
            });
        }

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && modalOverlay?.classList.contains("show")) {
                this.close();
            }
        });
    }
}

// ======================
// GESTIÓN DE PESTAÑAS
// ======================
class TabManager {
    static activateTab(tabId, paneId) {
        tabButtons.forEach(btn => btn.classList.remove("active"));
        tabPanes.forEach(pane => pane.classList.remove("active"));

        const selectedTab = document.getElementById(tabId);
        const selectedPane = document.getElementById(paneId);

        if (selectedTab) selectedTab.classList.add("active");
        if (selectedPane) selectedPane.classList.add("active");
    }

    static init() {
        tabButtons.forEach(btn => {
            btn.addEventListener("click", () => {
                const target = btn.getAttribute("data-target");
                if (target === "registerPane") {
                    this.activateTab("registerTab", "registerPane");
                } else if (target === "loginPane") {
                    this.activateTab("loginTab", "loginPane");
                }
            });
        });
    }
}

// ======================
// SISTEMA DE ALERTAS
// ======================
class AlertManager {
    static show(message, isSuccess = true) {
        if (!successAlert) return;

        successAlert.textContent = message;
        successAlert.style.background = isSuccess
            ? "linear-gradient(135deg, #10B981 0%, #059669 100%)"
            : "linear-gradient(135deg, #EF4444 0%, #DC2626 100%)";

        successAlert.classList.add("show");

        setTimeout(() => {
            successAlert.classList.remove("show");
        }, 3000);
    }
}

// ======================
// VALIDACIÓN DE FORMULARIOS
// ======================
class FormValidator {
    static validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    static validateURL(url) {
        if (!url) return true;
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    static validateRegistrationForm(formData) {
        const errors = [];
        if (!formData.organizationName?.trim()) errors.push("El nombre de la organización es requerido");
        if (!formData.email?.trim()) errors.push("El correo electrónico es requerido");
        else if (!this.validateEmail(formData.email)) errors.push("El formato del correo electrónico no es válido");
        if (!formData.phone?.trim()) errors.push("El teléfono es requerido");
        if (!formData.location?.trim()) errors.push("La ubicación es requerida");
        if (formData.website && !this.validateURL(formData.website)) errors.push("El formato del sitio web no es válido");
        return errors;
    }

    static validateLoginForm(formData) {
        const errors = [];
        if (!formData.usuario?.trim()) errors.push("El usuario es requerido");
        if (!formData.password?.trim()) errors.push("La contraseña es requerida");
        return errors;
    }

    static setupRealTimeValidation() {
        document.querySelectorAll('input[required]').forEach(input => {
            input.addEventListener('blur', function() {
                this.style.borderColor = this.value.trim() ? '#10b981' : '#ef4444';
            });
            input.addEventListener('input', function() {
                this.style.borderColor = '';
            });
        });
    }
}

// ======================
// GESTIÓN DE REGISTRO
// ======================
class RegistrationManager {
    static async submitRegistration(formData) {
        try {
            const response = await fetch("/OnClub/Backend/Api/Fundaciones.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(formData)
            });
            return await response.json();
        } catch (error) {
            console.error("Error en registro:", error);
            throw error;
        }
    }

    static init() {
        if (!registrationForm) return;

        registrationForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = {
                organizationName: document.getElementById("organizationName")?.value || "",
                email: document.getElementById("email")?.value || "",
                phone: document.getElementById("phone")?.value || "",
                location: document.getElementById("location")?.value || "",
                website: document.getElementById("website")?.value || "",
                description: document.getElementById("description")?.value || ""
            };

            const validationErrors = FormValidator.validateRegistrationForm(formData);
            if (validationErrors.length > 0) {
                AlertManager.show("❌ " + validationErrors[0], false);
                return;
            }

            const submitBtn = registrationForm.querySelector('button[type="submit"]');
            const originalText = submitBtn?.textContent;
            submitBtn.textContent = "Enviando...";
            submitBtn.disabled = true;

            try {
                const result = await this.submitRegistration(formData);
                if (result.status === "success") {
                    AlertManager.show("✅ Registro exitoso. Revisa tu correo.", true);
                    registrationForm.reset();
                    ModalManager.close();
                    setTimeout(() => ModalManager.open("login"), 2000);
                } else {
                    AlertManager.show("❌ " + (result.message || "Error en el registro"), false);
                }
            } catch {
                AlertManager.show("⚠️ Error de conexión con el servidor.", false);
            } finally {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    }
}

// ======================
// GESTIÓN DE LOGIN
// ======================
class LoginManager {
    static async submitLogin(credentials) {
        try {
            const response = await fetch("/OnClub/Backend/Api/Login.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(credentials)
            });
            return await response.json();
        } catch (error) {
            console.error("Error en login:", error);
            throw error;
        }
    }

    static init() {
        if (!loginForm) return;

        loginForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const credentials = {
                usuario: document.getElementById("loginUsuario")?.value || "",
                password: document.getElementById("loginPassword")?.value || ""
            };

            const validationErrors = FormValidator.validateLoginForm(credentials);
            if (validationErrors.length > 0) {
                AlertManager.show("❌ " + validationErrors[0], false);
                return;
            }

            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalText = submitBtn?.textContent;
            submitBtn.textContent = "Accediendo...";
            submitBtn.disabled = true;

            try {
                const result = await this.submitLogin(credentials);
                if (result.status === "success") {
                    AlertManager.show("¡Bienvenido " + (result.usuario?.nombre || "") + "!", true);
                    setTimeout(() => window.location.href = result.redirect, 1500);
                } else {
                    AlertManager.show("❌ " + (result.message || "Error en el login"), false);
                }
            } catch {
                AlertManager.show("⚠️ Error de conexión con el servidor.", false);
            } finally {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    }
}

// ======================
// GESTIÓN DE OLVIDO DE CONTRASEÑA
// ======================
class ForgotPasswordManager {
    static async submitForgotPassword(email) {
        try {
            const response = await fetch("/OnClub/Backend/Api/ForgotPassword.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email })
            });
            return await response.json();
        } catch (error) {
            console.error("Error en recuperación:", error);
            throw error;
        }
    }

    static init() {
        const forgotPasswordForm = document.getElementById("forgotPasswordForm");
        const forgotPasswordLink = document.getElementById("forgotPasswordLink");

        if (forgotPasswordLink) {
            forgotPasswordLink.addEventListener("click", (e) => {
                e.preventDefault();
                ModalManager.open("recover");
            });
        }

        if (forgotPasswordForm) {
            forgotPasswordForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                const email = document.getElementById("forgotEmail")?.value || "";

                if (!FormValidator.validateEmail(email)) {
                    AlertManager.show("❌ Ingresa un correo válido", false);
                    return;
                }

                const submitBtn = forgotPasswordForm.querySelector("button[type='submit']");
                const originalText = submitBtn.textContent;
                submitBtn.textContent = "Enviando...";
                submitBtn.disabled = true;

                try {
                    const result = await this.submitForgotPassword(email);
                    if (result.status === "success") {
                        AlertManager.show("✅ Revisa tu correo para restablecer la contraseña", true);
                        forgotPasswordForm.reset();
                        setTimeout(() => TabManager.activateTab("loginTab", "loginPane"), 2500);
                    } else {
                        AlertManager.show("❌ " + (result.message || "Error en la solicitud"), false);
                    }
                } catch {
                    AlertManager.show("⚠️ Error de conexión con el servidor.", false);
                } finally {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            });
        }
    }
}

// ======================
// INICIALIZACIÓN
// ======================
class OnClubApp {
    static init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initialize());
        } else {
            this.initialize();
        }
    }

    static initialize() {
        ModalManager.init();
        TabManager.init();
        FormValidator.setupRealTimeValidation();
        RegistrationManager.init();
        LoginManager.init();
        ForgotPasswordManager.init();

        const params = new URLSearchParams(window.location.search);
        if (params.get('open') === 'login') {
            ModalManager.open("login");
        }
    }
}

// ======================
// FUNCIONES GLOBALES
// ======================
function closeModal() {
    ModalManager.close();
}

// ======================
// INICIAR APP
// ======================
OnClubApp.init();
