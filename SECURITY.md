# Security concerns
Scheduled commands come with a few security concerns you should be aware of and deal with:

* The fact that a user is allowed to do something at the time of planning the command, doesn't mean they're allowed to do so when executing the command.
* The same is true the other way around.
* When the scheduled command runs, it usually does from a cli script. This means you can't do any checking against session data or the logged on user.

I see two approaches to dealing with these concerns.

## Store the user in the command
Include a reference to the user in the command. Use this to fetch the user and perform security checks at the time of execution (probably the easiest solution, but I think the design principles are a bit shaky here because you include information in a command that isn't strictly necessary for the command itself).

## Allow the execution to be blocked if something happens 
I'll explain this approach using an example:

Imagine an elected government, they're allowed to do certain things while being elected, but they can also pass a law. This law will remain in place after their period is over. Unless the new government removes the law, or passes a new one, the things described in the law remain active.

You can see a scheduled command as a law. If the user is allowed to pass it, it'll happen unless you do something to block it. There are two approaches to blocking the scheduled command:

* Use business logic. An example: Imagine a scheduled command allowing users to subscribe for last minute tickets to a concert. They'll get them at a huge discount unless there aren't any tickets left. You schedule the command to be executed after regular sales stop, the command executes and succeeds only if there are tickets left.
* Use a state machine. An example: Imagine a similar situation where a user is allowed to take an option on a ticket. The state machine allows passing from the 'option' state to the 'sold' state or to the 'cancelled' state. If any action between the scheduling of the command causes the state to transform from 'option' to 'cancelled', the transformation from 'option' to 'sold' is no longer valid and will fail. 
