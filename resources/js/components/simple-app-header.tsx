import { Link } from '@inertiajs/react';

interface SimpleAppHeaderProps {
  showNav: boolean;
  authenticated: any;
}

export function SimpleAppHeader({ showNav, authenticated }: SimpleAppHeaderProps) {
  if (!showNav) return null;

  return (
    <header className="fi-sidebar-header flex h-16 items-center bg-white px-6 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 lg:shadow-sm">
      <div className="flex items-center">
        <Link href="/" className="flex items-center text-xl font-bold leading-5 tracking-tight text-gray-950 dark:text-white">
          Bloggers
        </Link>
      </div>

      <div className="flex items-center gap-4 ml-auto">
        <Link
          href={authenticated ? route('dashboard') : route('login')}
          className="inline-block px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
        >
          {authenticated ? 'Dashboard' : 'Log in'}
        </Link>

        {!authenticated && (
          <Link
            href={route('register')}
            className="inline-block border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
          >
            Register
          </Link>
        )}
      </div>
    </header>
  );
}
