import { api } from "../../lib/api";

export type AdminDashboardStats = {
  products_count?: number;
  categories_count?: number;
  brands_count?: number;
  posts_count?: number;
  users_count?: number;
  orders_count?: number;
  contacts_count?: number;
  banners_count?: number;
  promotions_count?: number;
};

export async function getAdminDashboard() {
  const response = await api.get("/api/v1/admin/dashboard");
  return response.data;
}