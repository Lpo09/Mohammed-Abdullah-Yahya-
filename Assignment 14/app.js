// Grab elements
const input        = document.getElementById("userInput");
const unsafeBtn    = document.getElementById("unsafeBtn");
const safeBtn      = document.getElementById("safeBtn");
const unsafeOutput = document.getElementById("unsafeOutput");
const safeOutput   = document.getElementById("safeOutput");

// UNSAFE: inject raw HTML — vulnerable to DOM XSS
unsafeBtn.addEventListener("click", () => {
  const value = input.value;
  unsafeOutput.innerHTML = value; // ⚠️ DANGEROUS
});

// SAFE: treat input as plain text — neutralizes any HTML/JS
safeBtn.addEventListener("click", () => {
  const value = input.value;
  safeOutput.textContent = value; // ✅ SAFE
});

// Alternative SAFE approach using createElement + appendChild
function safeRender(element, text) {
  element.textContent = "";             // clear existing content
  const node = document.createElement("span");
  node.textContent = text;
  element.appendChild(node);
}