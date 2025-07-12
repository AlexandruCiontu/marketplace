import React from 'react';
import { Link } from '@inertiajs/react';

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginationProps {
  links: PaginationLink[];
}

export default function Pagination({ links }: PaginationProps) {
  // Don't render pagination if there's only 1 page
  if (links.length <= 3) {
    return null;
  }

  return (
    <div className="flex items-center justify-center my-4">
      <div className="join">
        {links.map((link, i) => {
          // Skip the "..." link
          if (link.label.includes('...')) {
            return (
              <span 
                key={i} 
                className="join-item btn btn-disabled"
                dangerouslySetInnerHTML={{ __html: link.label }}
              />
            );
          }

          // Skip the &laquo; and &raquo; symbols (Previous/Next)
          if (link.label.includes('&laquo;') || link.label.includes('&raquo;')) {
            const isPrevious = link.label.includes('&laquo;');
            return (
              link.url === null ? (
                <button 
                  key={i} 
                  className="join-item btn btn-disabled" 
                  disabled
                >
                  {isPrevious ? '«' : '»'}
                </button>
              ) : (
                <Link
                  key={i}
                  href={link.url}
                  className="join-item btn"
                  preserveScroll
                >
                  {isPrevious ? '«' : '»'}
                </Link>
              )
            );
          }

          // Regular page links
          return (
            <Link
              key={i}
              href={link.url ?? '#'}
              className={`join-item btn ${link.active ? 'btn-active' : ''}`}
              preserveScroll
            >
              {link.label}
            </Link>
          );
        })}
      </div>
    </div>
  );
}
