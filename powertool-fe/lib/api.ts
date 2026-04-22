import axios, { AxiosError } from "axios";

export const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000",
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
    "Content-Type": "application/json",
  },
});

export async function ensureCsrfCookie() {
  return api.get("/sanctum/csrf-cookie", {
    headers: {
      Accept: "application/json",
    },
  });
}

export function getApiErrorMessage(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as
      | { message?: string; error?: string }
      | undefined;

    return data?.message || data?.error || error.message || "Có lỗi xảy ra.";
  }

  if (error instanceof Error) {
    return error.message;
  }

  return "Có lỗi không xác định.";
}