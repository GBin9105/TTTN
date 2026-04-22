import { create } from "zustand";

export type User = {
  id: number;
  name: string;
  email: string;
  role?: "admin" | "customer" | string;
  avatar?: string | null;
  phone?: string | null;
  address?: string | null;
};

type UserStore = {
  user: User | null;
  setUser: (user: User | null) => void;
  clearUser: () => void;
};

export const useUserStore = create<UserStore>((set) => ({
  user: null,
  setUser: (user) => set({ user }),
  clearUser: () => set({ user: null }),
}));