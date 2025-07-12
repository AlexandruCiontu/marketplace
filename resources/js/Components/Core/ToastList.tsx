import React, {useEffect, useRef, useState} from 'react';
import {usePage} from "@inertiajs/react";

function ToastList({pageProp, alertVariant}: { pageProp: 'errorToast' | 'successToast', alertVariant: string }) {
  const props = usePage().props;

  const [messages, setMessages] = useState<any[]>([]);
  const timeoutRefs = useRef<{ [key: number]: ReturnType<typeof setTimeout> }>({}); // Store timeouts by message ID

  useEffect(() => {
    if (props[pageProp].message) {
      const newMessage = {
        ...props[pageProp],
        id: props[pageProp].time, // Use time as unique identifier
      };

      // Add the new message to the list
      setMessages((prevMessages) => [newMessage, ...prevMessages]);

      // Store the timeout ID in the ref
      timeoutRefs.current[newMessage.id] = setTimeout(() => {
        // Use a functional update to ensure the latest state is used
        setMessages((prevMessages) =>
          prevMessages.filter((msg) => msg.id !== newMessage.id)
        );
        // Clear timeout from refs after execution
        delete timeoutRefs.current[newMessage.id];
      }, 5000);
    }

  }, [props[pageProp]]);

  return (
    messages.length > 0 && (
      <div className="toast toast-top toast-end z-[1000] mt-16">
        {messages.map((msg) => (
          <div className={`alert alert-${alertVariant}`} key={msg.id}>
            <span>{msg.message}</span>
          </div>
        ))}
      </div>
    )
  );
}

export default ToastList;
