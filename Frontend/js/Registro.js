// ======================
// VARIABLES GLOBALES
// ======================
const modalOverlay = document.getElementById("modalOverlay");
const openModalBtn = document.getElementById("openModalBtn");
const closeModalBtn = document.getElementById("closeModalBtn");
const tabButtons = document.querySelectorAll(".tab-btn");
const tabPanes = document.querySelectorAll(".tab-pane");

const registrationForm = document.getElementById("registrationForm");
const loginForm = document.getElementById("loginForm");
const emailForm = document.getElementById("emailForm");
const codeForm = document.getElementById("codeForm");
const passwordForm = document.getElementById("passwordForm");

const successAlert = document.getElementById("successAlert");

const passwordRecoveryModal = document.getElementById("passwordRecoveryModal");
const openForgotPassword = document.getElementById("openForgotPassword");
const closeRecoveryBtn = document.getElementById("closeRecoveryBtn");

const stepEmail = document.getElementById("stepEmail");
const stepCode = document.getElementById("stepCode");
const stepNewPassword = document.getElementById("stepNewPassword");
const emailDisplay = document.getElementById("emailDisplay");
const countdownElement = document.getElementById("countdown");

const registerTab = document.getElementById("registerTab");
const loginTab = document.getElementById("loginTab");
const registerPane = document.getElementById("registerPane");
const loginPane = document.getElementById("loginPane");

const backToLoginFromEmailBtn = document.getElementById("backToLoginFromEmail");
const backToEmailBtn = document.getElementById("backToEmail");
const backToCodeBtn = document.getElementById("backToCode");
const resendCodeBtn = document.getElementById("resendCode");

let countdownTimer;
let countdownValue = 300;

// ======================
// CONFIG API
// ======================
const API_BASE_URL = "../../Backend/Api";
const API_ENDPOINTS = {
    REGISTER: `${API_BASE_URL}/Fundaciones.php`,
    LOGIN: `${API_BASE_URL}/Login.php`,
    FORGOT_PASSWORD: `${API_BASE_URL}/ForgotPassword.php`,
    VERIFY_CODE: `${API_BASE_URL}/verify_code.php`,
    RESET_PASSWORD: `${API_BASE_URL}/reset_password.php`
};

// ======================
// ABRIR Y CERRAR MODAL
// ======================
openModalBtn.addEventListener("click", () => {
    modalOverlay.classList.add("show");
});

closeModalBtn.addEventListener("click", () => {
    modalOverlay.classList.remove("show");
});

// Cerrar modal haciendo clic fuera del contenedor
modalOverlay.addEventListener("click", (e) => {
    if (e.target === modalOverlay) {
        modalOverlay.classList.remove("show");
    }
});

// ======================
// FUNCIONES BÁSICAS
// ======================
function openModal() {
    modalOverlay.classList.add("active");
    document.body.style.overflow = "hidden"; // evita scroll detrás
}

function closeModal() {
    modalOverlay.classList.remove("active");
    document.body.style.overflow = ""; // restaura scroll
}

function showAlert(message, type = "success") {
    successAlert.textContent = message;
    successAlert.className = `success-alert ${type}`;
    successAlert.style.display = "block";
    setTimeout(() => (successAlert.style.display = "none"), 3000);
}

async function makeApiRequest(url, data) {
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });

        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        return await response.json();
    } catch (error) {
        showAlert(`Error en la conexión: ${error.message}`, "error");
        return null;
    }
}

// ======================
// TABS
// ======================
function activarTab(tab, pane) {
    // quitar active de tabs
    registerTab.classList.remove("active");
    loginTab.classList.remove("active");

    // quitar active de panes
    registerPane.classList.remove("active");
    loginPane.classList.remove("active");

    // activar el tab y el pane seleccionados
    tab.classList.add("active");
    pane.classList.add("active");
}

registerTab.addEventListener("click", () => {
    activarTab(registerTab, registerPane);
});

loginTab.addEventListener("click", () => {
    activarTab(loginTab, loginPane);
});

// ======================
// REGISTRO
// ======================
registrationForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const btn = registrationForm.querySelector("button[type='submit']");
    btn.disabled = true;
    btn.textContent = "Enviando...";

    const data = {
        organizationName: registrationForm.organizationName.value,
        email: registrationForm.email.value,
        phone: registrationForm.phone.value,
        location: registrationForm.location.value,
        website: registrationForm.website.value,
        description: registrationForm.description.value
    };

    const result = await makeApiRequest(API_ENDPOINTS.REGISTER, data);
    console.log("Respuesta del backend (Registro):", result);

    btn.disabled = false;
    btn.textContent = "Registrar";

    if (result?.status === "success") {
        showAlert("Registro exitoso ✅", "success");
        registrationForm.reset();

        // 👉 Si el backend devuelve redirect, mandamos al usuario allí
        if (result.redirect) {
            window.location.href = result.redirect;
        } else {
            // Si no devuelve redirect, simplemente pasamos a login
            activarTab(loginTab, loginPane);
        }
    } else {
        showAlert(result?.message || "Error en el registro ❌", "error");
    }
});

// ======================
// LOGIN
// ======================
loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = {
        usuario: loginForm.loginUsuario.value,
        password: loginForm.loginPassword.value
    };

    const result = await makeApiRequest(API_ENDPOINTS.LOGIN, data);
    console.log("Respuesta del backend (Login):", result);

    if (result?.status === "success") {
        showAlert("Login exitoso ✅", "success");
        loginForm.reset();
        closeModal();

        // 👉 Redirección según lo que devuelva el backend
        if (result.redirect) {
            window.location.href = result.redirect;
        } else {
            window.location.reload(); // si no devuelve nada, recargamos
        }
    } else {
        showAlert(result?.message || "Credenciales incorrectas ❌", "error");
    }
});

// ======================
// RECUPERAR CONTRASEÑA
// ======================
function startCountdown() {
    clearInterval(countdownTimer);
    countdownValue = 300;

    countdownTimer = setInterval(() => {
        let minutes = Math.floor(countdownValue / 60);
        let seconds = countdownValue % 60;
        countdownElement.textContent = `${minutes
            .toString()
            .padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
        countdownValue--;

        if (countdownValue < 0) clearInterval(countdownTimer);
    }, 1000);
}

// abrir/cerrar modal recuperación
function openRecoveryModal() {
    passwordRecoveryModal.classList.add("show");
    document.body.style.overflow = "hidden";
}

function closeRecoveryModal() {
    passwordRecoveryModal.classList.remove("show");
    document.body.style.overflow = "";
}

openForgotPassword.addEventListener("click", (e) => {
    e.preventDefault();
    closeModal();
    setTimeout(openRecoveryModal, 300);
});

closeRecoveryBtn.addEventListener("click", closeRecoveryModal);

passwordRecoveryModal.addEventListener("click", (e) => {
    if (e.target === passwordRecoveryModal) closeRecoveryModal();
});

document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && passwordRecoveryModal.classList.contains("show")) {
        closeRecoveryModal();
    }
});

// ======================
// FUNCIONES RECUPERACIÓN
// ======================
async function sendVerificationCode(email) {
    return await makeApiRequest(API_ENDPOINTS.FORGOT_PASSWORD, { email });
}

async function verifyCode(email, code) {
    return await makeApiRequest(API_ENDPOINTS.VERIFY_CODE, { email, code });
}

async function resetPassword(email, password) {
    return await makeApiRequest(API_ENDPOINTS.RESET_PASSWORD, { email, password });
}

function showRecoveryStep(stepId) {
    stepEmail.classList.remove("active");
    stepCode.classList.remove("active");
    stepNewPassword.classList.remove("active");
    document.getElementById(stepId).classList.add("active");
}
// ======================
// FLUJO RECUPERACIÓN
// ======================
// Paso 1: enviar código
emailForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const email = document.getElementById("recoveryEmail").value.trim();

    if (!validateEmail(email)) {
        showAlert("Por favor, ingresa un correo electrónico válido", "error");
        return;
    }

    recoveryEmail = email;
    emailDisplay.textContent = email;

    const submitBtn = emailForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Enviando...";
    submitBtn.disabled = true;

    try {
        const result = await sendVerificationCode(email);
        showAlert(result.message, result.status === "success" ? "success" : "error");

        if (result.status === "success") {
            showRecoveryStep("stepCode");
            startCountdown();
        }
    } catch (error) {
        showAlert("Error al enviar el código. Intenta nuevamente.", "error");
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});

// Paso 2: verificar código
codeForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const code = document.getElementById("verificationCode").value.trim();

    if (!code || code.length !== 6) {
        showAlert("Por favor, ingresa un código de 6 dígitos", "error");
        return;
    }

    const submitBtn = codeForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Verificando...";
    submitBtn.disabled = true;

    try {
        const result = await verifyCode(recoveryEmail, code);
        showAlert(result.message, result.status === "success" ? "success" : "error");

        if (result.status === "success") {
            showRecoveryStep("stepNewPassword");
            clearInterval(countdownTimer);
        }
    } catch (error) {
        showAlert("Error al verificar el código. Intenta nuevamente.", "error");
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});

// Paso 3: cambiar contraseña
passwordForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    const validationError = validatePassword(newPassword, confirmPassword);
    if (validationError) {
        showAlert(validationError, "error");
        return;
    }

    const submitBtn = passwordForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Cambiando...";
    submitBtn.disabled = true;

    try {
        const result = await resetPassword(recoveryEmail, newPassword);
        showAlert(result.message, result.status === "success" ? "success" : "error");

        if (result.status === "success") {
            setTimeout(() => {
                closeRecoveryModal();
                openModal("login");
            }, 1500);
        }
    } catch (error) {
        showAlert("Error al cambiar la contraseña. Intenta nuevamente.", "error");
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});
// ======================
// VALIDACIONES
// ======================
function validateEmail(email) {
    // Regex básico para validar correos
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePassword(password, confirmPassword) {
    if (password.length < 6) {
        return "La contraseña debe tener al menos 6 caracteres";
    }
    if (password !== confirmPassword) {
        return "Las contraseñas no coinciden";
    }
    return null; // No hay error
}
// Volver al login: cierra modal de recuperación y abre modal principal en pestaña Login
if (backToLoginFromEmailBtn) {
  backToLoginFromEmailBtn.addEventListener("click", (e) => {
    e.preventDefault();
    closeRecoveryModal();
    // abrir modal principal y activar login después de un pequeño delay
    setTimeout(() => {
      openModal(); // abre modal principal
      activarTab(loginTab, loginPane); // activa pestaña login
    }, 200);
  });
}

// Volver a paso Email (desde Código)
if (backToEmailBtn) {
  backToEmailBtn.addEventListener("click", (e) => {
    e.preventDefault();
    // Parar contador y volver al paso de email
    clearInterval(countdownTimer);
    showRecoveryStep("stepEmail");
  });
}

// Volver a paso Código (desde Nueva Contraseña)
if (backToCodeBtn) {
  backToCodeBtn.addEventListener("click", (e) => {
    e.preventDefault();
    showRecoveryStep("stepCode");
    // Reiniciar contador si hace falta
    startCountdown();
  });
}
const toggleLoginPassword = document.getElementById("toggleLoginPassword");
const loginPassword = document.getElementById("loginPassword");

toggleLoginPassword.addEventListener("click", () => {
  const type = loginPassword.getAttribute("type") === "password" ? "text" : "password";
  loginPassword.setAttribute("type", type);

  // Cambia el icono según estado
  toggleLoginPassword.textContent = type === "password" ? "👁️" : "🙈";
});
// Ingreso de login desde el Home
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');

    if (tab === 'login') {
        // Mostrar el modal
        const modalOverlay = document.getElementById('modalOverlay');
        modalOverlay.classList.add('show'); 

        // Activar pestaña de login
        const loginTab = document.getElementById('loginTab');
        const loginPane = document.getElementById('loginPane');
        const registerTab = document.getElementById('registerTab');
        const registerPane = document.getElementById('registerPane');

        // Quitar active del registro
        registerTab.classList.remove('active');
        registerPane.classList.remove('active');

        // Agregar active al login
        loginTab.classList.add('active');
        loginPane.classList.add('active');
    }
});
// ======================
// BOTONES MODAL PRINCIPAL
// ======================
openModalBtn.addEventListener("click", openModal);
closeModalBtn.addEventListener("click", closeModal);
