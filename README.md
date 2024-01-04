# RDBMS Assignment
This assignment aims to assess your abilities in the semantics of RDBMS, SQL and ORM of Laravel.
You will have approximately 2 hours to accomplish. During your work , you have access to internet. 
Don't forget, you are not allowed to use AI tools. Additionally, try not to ask your peers about your challenges.
An ER diagram is provided for you which just includes name of tables and properties without their datatype. 


## Business Statement: 
INBO Shop has a plan to implement a some new functionalities for selling Instagram products.
we will need to save our customer data. Laravel provided users table will be enough for this purpose. 
Also, we should save the data of our products. We will have two type of products in our system, being Instagram pages 
and Instagram Followers, which both have different attributes.    
An Instagram Page will have : 
 - followers count 
 - following count 
 - posts count 
 - visibility
 - username
 - bio 

An Instagram Follower Product will have : 
- price per follower(which is in dollar)
- provider name
- service quality(1 to 10)
- speed of follower charging

other data is provided for you in the ER diagram. 


### task 1 : 
Define true relationships between the tables due to business statement of the project.   

### task 2 : 
Now, it is time to define relationships through your migrations. define the with appropriate data type. 

### task 3: 
Define your models with needed relations in them. Also, define your factories and be ready for the next level. 


