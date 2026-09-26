// Calls into NativePHP mobile's bridge (see plugins/) from the webview.
// Shared across features — learning, and anything added later (chat,
// calling, ...) that needs vibration, native TTS, push, camera, etc.
export async function nativeBridgeCall(
    method: string,
    params: Record<string, unknown> = {},
): Promise<unknown> {
    const response = await fetch("/_native/api/call", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN":
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") || "",
        },
        body: JSON.stringify({ method, params }),
    });
    const result = await response.json();
    if (result.status === "error") {
        throw new Error(result.message || "Native call failed");
    }
    const data = result.data;
    return data && typeof data === "object" && "data" in data
        ? data.data
        : data;
}

// A short native tap buzz (Device.Vibrate is core to nativephp/mobile, no
// plugin needed) — silently does nothing outside the native shell (desktop
// browser dev preview).
export function vibrate(): void {
    nativeBridgeCall("Device.Vibrate").catch(() => {});
}
