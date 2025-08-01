import {Media} from "@/types";
import {useEffect, useState} from "react";
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  ChevronUpIcon,
  ChevronDownIcon,
} from "@heroicons/react/24/outline";

function Carousel({media}: { media: Media[] }) {
  const [selectedMedia, setSelectedMedia] = useState<Media>(media[0]);
  const [isLargeScreen, setIsLargeScreen] = useState(false);
  const [thumbnailStartIndex, setThumbnailStartIndex] = useState(0);
  const [thumbnailsPerView, setThumbnailsPerView] = useState(3); // default for small screens

  useEffect(() => {
    const updateScreenSize = () => {
      if (window.innerWidth >= 640) {
        setIsLargeScreen(true);
        setThumbnailsPerView(6); // Show more thumbnails on large screens
      } else {
        setIsLargeScreen(false);
        if (window.innerWidth >= 550) {
          setThumbnailsPerView(5);
        } else if (window.innerWidth >= 470) {
          setThumbnailsPerView(4);
        } else if (window.innerWidth >= 400) {
          setThumbnailsPerView(3);
        } else {
          setThumbnailsPerView(2);
        }
      }
    };
    updateScreenSize();
    window.addEventListener("resize", updateScreenSize);
    return () => window.removeEventListener("resize", updateScreenSize);
  }, []);

  useEffect(() => {
    setSelectedMedia(media[0]);
    setThumbnailStartIndex(0);
  }, [media]);

  const canGoPrev = thumbnailStartIndex > 0;
  const canGoNext = thumbnailStartIndex + thumbnailsPerView < media.length;

  const visibleThumbnails = media.slice(
    thumbnailStartIndex,
    thumbnailStartIndex + thumbnailsPerView
  );

  const goPrev = () => {
    if (canGoPrev) {
      setThumbnailStartIndex((prev) => Math.max(prev - 1, 0));
    }
  };

  const goNext = () => {
    if (canGoNext) {
      setThumbnailStartIndex((prev) =>
        Math.min(prev + 1, media.length - thumbnailsPerView)
      );
    }
  };

  // Determine layout and icons based on screen size
  const containerClass = isLargeScreen ? "flex-col" : "flex-row";
  const iconPrev = isLargeScreen ? (
    <ChevronUpIcon className="h-5 w-5"/>
  ) : (
    <ChevronLeftIcon className="h-5 w-5"/>
  );
  const iconNext = isLargeScreen ? (
    <ChevronDownIcon className="h-5 w-5"/>
  ) : (
    <ChevronRightIcon className="h-5 w-5"/>
  );

  return (
    <div className="flex flex-col sm:flex-row items-start gap-4 md:gap-8">
      {/* Thumbnails Section */}
      <div
        className={`order-2 sm:order-1 flex ${containerClass} items-center gap-2 py-2`}
      >
        {/* Previous Button */}
        <button
          onClick={goPrev}
          disabled={!canGoPrev}
          className={`p-2 rounded-full border ${
            canGoPrev ? "hover:bg-gray-200" : "opacity-50 cursor-not-allowed"
          }`}
        >
          {iconPrev}
        </button>

        {/* Thumbnails Container */}
        <div
          className={`flex ${
            isLargeScreen ? "flex-col" : "flex-row"
          } gap-2 items-center`}
        >
          {visibleThumbnails.map((item) => (
            <button
              key={item.id}
              onClick={() => setSelectedMedia(item)}
              className={
                "border-2 p-1 " +
                (selectedMedia.id === item.id
                  ? "border-blue-500"
                  : "hover:border-blue-500")
              }
            >
              {"thumb" in item ? (
                <img
                  src={item.thumb}
                  alt=""
                  className="w-[64px] h-[64px] min-w-[64px] object-contain"
                />
              ) : (
                <video
                  src={item.url}
                  className="w-[64px] h-[64px] min-w-[64px] object-cover"
                  muted
                />
              )}
            </button>
          ))}
        </div>

        {/* Next Button */}
        <button
          onClick={goNext}
          disabled={!canGoNext}
          className={`p-2 rounded-full border ${
            canGoNext ? "hover:bg-gray-200" : "opacity-50 cursor-not-allowed"
          }`}
        >
          {iconNext}
        </button>
      </div>

      {/* Selected Image Section */}
      <div className="order-1 sm:order-2 carousel w-full">
        <div className="carousel-item w-full">
          {"thumb" in selectedMedia ? (
            <img
              src={selectedMedia.large}
              className="max-w-full h-auto md:h-[600px] mx-auto object-contain"
              alt="Selected"
            />
          ) : (
            <video
              src={selectedMedia.url}
              className="max-w-full h-auto md:h-[600px] mx-auto"
              controls
            />
          )}
        </div>
      </div>
    </div>
  );
}

export default Carousel;
