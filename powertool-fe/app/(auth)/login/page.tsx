"use client";

import { useState } from "react";
import authService from "../../../services/auth.service";
import { getApiErrorMessage } from "../../../lib/api";

export default function AdminLoginPage() {
  const [email, setEmail] = useState("admin@powertools.com");
  const [password, setPassword] = useState("12345678");
  const [loading, setLoading] = useState(false);
  const [errorText, setErrorText] = useState("");

  async function handleLogin() {
    setLoading(true);
    setErrorText("");

    try {
      const result = await authService.login({ email, password });

      if (!result.user) {
        throw new Error("Không lấy được thông tin người dùng từ /me.");
      }

      if (String(result.user.role || "").toLowerCase() !== "admin") {
        throw new Error("Tài khoản này không có quyền quản trị.");
      }

      localStorage.setItem("powertool_user", JSON.stringify(result.user));
      window.location.href = "/admin/dashboard";
    } catch (error) {
      setErrorText(getApiErrorMessage(error));
    } finally {
      setLoading(false);
    }
  }

  return (
    <main className="flex min-h-screen items-center justify-center bg-gray-100 px-4">
      <div className="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
        <h1 className="text-3xl font-bold text-gray-900">Admin Login</h1>
        <p className="mt-2 text-sm text-gray-500">
          Đăng nhập để truy cập trang quản trị Power Tools.
        </p>

        <div className="mt-6 space-y-4">
          <div>
            <label className="mb-2 block text-sm font-medium text-gray-900">
              Email
            </label>
            <input
              type="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              className="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-black"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-gray-900">
              Mật khẩu
            </label>
            <input
              type="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              className="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-black"
            />
          </div>

          {errorText ? (
            <div className="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">
              {errorText}
            </div>
          ) : null}

          <button
            type="button"
            onClick={handleLogin}
            disabled={loading}
            className="w-full rounded-xl bg-black px-4 py-3 font-medium text-white transition hover:bg-gray-800 disabled:opacity-60"
          >
            {loading ? "Đang đăng nhập..." : "Đăng nhập quản trị"}
          </button>
        </div>
      </div>
    </main>
  );
}