# brainwave-svm-login
Emotiv Brainwave SVM-based Login 

## Requirements
* Webserver running PHP (PHP 7.0 with lighttdp tested)
* (probably) Re-combile libsvm (svm-predict)

## Video
https://youtu.be/L26Gelkhkf4

## Preparing the data

The original data looks something like:

Channel,Theta,Alpha,Lowbeta,Highbeta,Gamma

AF3,621.4072977,794.9707577,167.8989619,431.5566596,462.8267375

T7,588.8385383,1002.008753,143.9251969,503.5703621,611.768041 

First we need to prepare the data.

Right now we have 2 users "meteor" and "rumba". Each user has 10 'samples' collected from different experiments. First, remove 2 of$

Combine the remaining samples into libsvm format (column 4 and 5 are high/low beta):
`cat * | awk -F',' '{print "1 1:" $4 " 2:" $5}' | grep -v beta > meteor.single.train.libsvm`

This code means that the user meteor has a label of "1". If you will be creating a multi-class classifer, other users need to have a different label.

If you get a bunch of empty lines (I did) then you can remove them with this:
`cat meteor.single.train.libsvm | grep -v "1 1: 2:" > meteor.single.train.clean.libsvm`

Now you should have a very large training set FOR EACH USER.

If each user have been assigned different labels, then you can combine them.

### Scaling
First scale: `svm-scale -l -1 -u 1 -s range meteor.single.train.clean.libsvm > meteor.single.train.clean.libsvm.scale`
After first, reuse 'range' file: `svm-scale -r range rumba.single.train.clean.libsvm > rumba.single.train.clean.libsvm.scale`

### Training
For multi-class, I used default training settings: `svm-train multi.train.clean.libsvm.scale` This produces "multi.train.clean.libsvm.scale.model" that can be used for classification of both users.

For one class: `svm-train -s 2 -n 0.2 meteor.single.train.clean.libsvm.scale`  One model is produced for each user.

### Test data
Test data goes through the same libsvm format and scaling process. Do not combine test data with other data. Make sure you know which test data belongs to which user.

Once test data has been scaled, and the model has been trained: `svm-predict meteor.single.test.clean.libsvm.scale rumba.single.train.clean.libsvm.scale.model predict.out`  Here we are using meteor data, and trying to classify it with rumba's model. It *should* result in a "-1". If meteors data is used with meteors model it *should* result in a "1". (One-class)

For multi-class, if meteor has a label of 1 and rumba has a label of 2, meteors data should produce "1" and rumbas data should procuce "2".

The way the data is set up, each line of "predict.out" will be classified. For a quick and dirty solution, I am taking the average of the classified values `awk '{s+=$1}END{print NR}' RS=" " predict.out`  So far, this method has worked very well.

We could also calculate what the error rate was, and cut off at a certain point.
