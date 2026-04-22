import { api, ensureCsrfCookie } from "../lib/api";

export type LoginPayload = {
  email: string;
  password: string;
};

export type User = {
  id: number;
  name: string;
  email: string;
  role?: "admin" | "customer" | string;
  avatar?: string | null;
  phone?: string | null;
  address?: string | null;
  status?: boolean;
};

function extractUser(payload: unknown): User | null {
  if (!payload || typeof payload !== "object") return null;

  const data = payload as Record<string, unknown>;

  if (data.user && typeof data.user === "object") {
    return data.user as User;
  }

  if (data.data && typeof data.data === "object") {
    const nested = data.data as Record<string, unknown>;

    if (nested.user && typeof nested.user === "object") {
      return nested.user as User;
    }

    if ("id" in nested && "email" in nested) {
      return nested as unknown as User;
    }
  }

  if ("id" in data && "email" in data) {
    return data as unknown as User;
  }

  return null;
}

const authService = {
  async login(payload: LoginPayload) {
    await ensureCsrfCookie();

    const loginResponse = await api.post("/api/v1/auth/login", payload);
    const meResponse = await api.get("/api/v1/auth/me");

    return {
      loginResponse: loginResponse.data,
      meResponse: meResponse.data,
      user: extractUser(meResponse.data),
    };
  },

  async logout() {
    await ensureCsrfCookie();
    return api.post("/api/v1/auth/logout");
  },
};

export default authService;