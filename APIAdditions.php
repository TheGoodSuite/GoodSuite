
\Good\Manners\Storage:

deleteAny($condition);
deleteAll(); //? (truncate, allows resetting of indexes)
count($condition);

null for condition to allow "any in storage" (in a number of places)


\Good\Manners\Resolver:

setFromArray($condition);

\Good\Manners\Condition:

createConditionFromArray(); // or something like that... possibly on some object
