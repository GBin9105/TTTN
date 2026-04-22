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

const STORAGE_KEY = "powertool_user";

export function saveUserToStorage(user: User) {
  if (typeof window === "undefined") return;
  localStorage.setItem(STORAGE_KEY, JSON.stringify(user));
}

export function getUserFromStorage(): User | null {
  if (typeof window === "undefined") return null;

  const raw = localStorage.getItem(STORAGE_KEY);
  if (!raw) return null;

  try {
    return JSON.parse(raw) as User;
  } catch {
    return null;
  }
}

export function clearUserFromStorage() {
  if (typeof window === "undefined") return;
  localStorage.removeItem(STORAGE_KEY);
}

export function isAdminUser(user: User | null) {
  return String(user?.role || "").toLowerCase() === "admin";
}