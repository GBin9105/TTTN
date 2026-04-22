import Link from "next/link";

export default function HomePage() {
  return (
    <main className="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-100 via-gray-50 to-slate-200">
      <div className="absolute inset-0">
        <div className="absolute -left-24 top-10 h-72 w-72 rounded-full bg-black/5 blur-3xl" />
        <div className="absolute -right-20 bottom-10 h-80 w-80 rounded-full bg-slate-400/10 blur-3xl" />
      </div>

      <div className="relative mx-auto flex min-h-screen max-w-6xl items-center justify-center px-4 py-10">
        <div className="grid w-full items-center gap-10 lg:grid-cols-2">
          <div className="space-y-6">


            <div className="space-y-4">
              <h1 className="text-4xl font-bold leading-tight text-gray-900 md:text-5xl">
                Frontend cho hệ thống
                <span className="block text-black">Power Tools</span>
              </h1>


            </div>

            <div className="flex flex-col gap-3 sm:flex-row">
              <Link
                href="account/login"
                className="inline-flex items-center justify-center rounded-xl bg-black px-6 py-3 text-sm font-semibold text-white transition hover:bg-gray-800"
              >
                Đăng nhập user
              </Link>

              <Link
                href="/login"
                className="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-900 transition hover:bg-gray-100"
              >
                Đăng nhập admin
              </Link>
            </div>
          </div>
        </div>
      </div>
    </main>
  );
}