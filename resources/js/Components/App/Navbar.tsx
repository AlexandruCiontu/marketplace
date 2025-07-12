import React, {FormEventHandler, useState} from 'react';
import {Link, usePage} from "@inertiajs/react";
import MiniCartDropdown from "@/Components/App/MiniCartDropdown";
import {MagnifyingGlassIcon} from "@heroicons/react/24/outline";
import {hasAnyRole, hasRole} from "@/helpers";
import {useSearchBox} from "react-instantsearch";

function Navbar() {
  const params = new URLSearchParams(window.location.search);
  const query = params.get('products_index[query]');

  const {auth, departments} = usePage().props;
  const {user} = auth;
  const {refine} = useSearchBox();
  const [value, setValue] = useState(query || '');

  const searchFormComponent = (className = 'hidden md:flex') => {
    return (
      <form onSubmit={onSubmit} className={'join flex-1 ' + className}>
        <div className="flex-1">
          <input
            value={value}
            onChange={(e) => setValue(e.target.value)}
            onBlur={onSubmit}
            className="input input-bordered join-item w-full" placeholder="Search"/>
        </div>
        <div className="indicator">
          <button className="btn join-item">
            <MagnifyingGlassIcon className={'size-4'}/>
            <span className={"hidden md:inline-flex"}>Search</span>
          </button>
        </div>
      </form>
    )
  }

  const onSubmit: FormEventHandler = (e) => {
    e.preventDefault();
    refine(value)
  };

  return (
    <>
      <div className="navbar bg-base-100">
        <div className="flex-1">
          <Link href="/" className="btn btn-ghost text-xl">LaraStore</Link>
        </div>
        <div className="flex-none gap-4">
          {searchFormComponent()}
          <MiniCartDropdown/>
          {user && <div className="dropdown dropdown-end">
            <div tabIndex={0} role="button" className="btn btn-ghost btn-circle avatar">
              <div className="w-10 rounded-full">
                <img
                  alt="Tailwind CSS Navbar component"
                  src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp"/>
              </div>
            </div>
            <ul
              tabIndex={0}
              className="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1000] mt-3 w-52 p-2 shadow">
              <li>
                <Link href={route('profile.edit')} className="justify-between">
                  Profile
                </Link>
              </li>
              {hasAnyRole(auth.user, ['Admin', 'Vendor']) &&
                <li>
                  <a href="/admin" className="justify-between">
                    {hasRole(auth.user, 'Admin') ? 'Admin Area' : 'Vendor Area'}
                  </a>
                </li>
              }
              <li>
                <Link href={route('orders.index')} className="justify-between">
                  My Orders
                </Link>
              </li>
              <li>
                <Link href={route('shippingAddress.index')} className="justify-between">
                  My Addresses
                </Link>
              </li>
              <li>
                <Link href={route('logout')} method={"post"} as="button">
                  Logout
                </Link>
              </li>
            </ul>
          </div>}
          {!user && <>
            <Link href={route('login')} className={"btn"}>Login</Link>
            <Link href={route('register')} className={"btn btn-primary"}>
              Register
            </Link>
          </>}
        </div>
      </div>
      <div className=" md:hidden navbar bg-base-100">
        <div className="navbar-center flex justify-between flex-1">
          {searchFormComponent('flex')}
        </div>
      </div>
    </>
  );
}

export default Navbar;
