async function loadFragment(targetId, fileName) {
    const target = document.getElementById(targetId);
    if (!target) {
        return;
    }

    try {
        const response = await fetch(fileName, { cache: "no-store" });
        if (!response.ok) {
            throw new Error("Failed to load " + fileName + ": " + response.status);
        }

        target.innerHTML = await response.text();
    } catch (error) {
        console.error("Layout fragment error:", error);
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    await loadFragment("site-header", "header.html");
    await loadFragment("site-footer", "footer.html");
});
