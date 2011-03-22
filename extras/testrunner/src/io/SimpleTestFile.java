package io;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.LinkedList;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Sitting more or less at the lowest level of this program, this class
 * accepts a file as a constructor argument and parses that file to check
 * if it is a ThinkUp test file and what test methods it contains.
 * 
 * @author Sam Rose samwho@lbak.co.uk
 */
public class SimpleTestFile implements Comparable {

    /**
     * This class's implementation of the Comparable class compares the
     * name of the file it parsed with the name of another parsed file.
     *
     * Basically it checks for lexicographical order.
     * 
     * @param o
     * @return
     */
    public int compareTo(Object o) {
        return this.file.getName().compareTo(((SimpleTestFile) o).getFile().getName());
    }
    private File file;
    private LinkedList<String> methods = new LinkedList<String>();
    private boolean isTestFile = false;
    private static Pattern thinkupTestFilePattern = Pattern.compile(" extends (?:ThinkUp(?:Basic)?(?:Unit|Web)TestCase)");
    private static Pattern methodPattern = Pattern.compile("(?:public )?function (test.+)(?: )?[(][)]");

    /**
     * Construct the class with a file path to scan.
     * 
     * @param filePath The file to scan for test methods.
     */
    public SimpleTestFile(String filePath) {
        this.file = new File(filePath);
        this.parseFile(this.file);
    }

    /**
     * Parses the file, checking if it is a test file and making a list of the
     * test methods it contains.
     *
     * @param file The file to parse.
     */
    private void parseFile(File file) {
        if (file.isFile()) {
            Matcher thinkupTestFileMatcher;
            Matcher methodMatcher;

            try {
                BufferedReader br = new BufferedReader(new FileReader(file));

                String line = br.readLine();

                while (line != null) {
                    thinkupTestFileMatcher = thinkupTestFilePattern.matcher(line);
                    methodMatcher = methodPattern.matcher(line);

                    if (thinkupTestFileMatcher.find()) {
                        this.isTestFile = true;
                    } else if (methodMatcher.find()) {
                        methods.add(methodMatcher.group(1));
                    }

                    line = br.readLine();
                }

                br.close();
            } catch (FileNotFoundException e) {
                e.printStackTrace();
            } catch (IOException e) {
                e.printStackTrace();
            }
        }
    }

    /**
     * Gets a list of the test methods that were found in
     * this file.
     *
     * @return a LinkedList of test methods found in this file.
     */
    public LinkedList<String> getMethods() {
        return methods;
    }

    /**
     * A method to check if this file is a ThinkUp unit test.
     * 
     * @return True if the file is a test file, false otherwise.
     */
    public boolean isTestFile() {
        return this.isTestFile;
    }

    /**
     * Gets the file object that this class is using.
     *
     * @return The file object that this class parsed.
     */
    public File getFile() {
        return file;
    }

    @Override
    public String toString() {
        return this.file.getName();

//        String returnString = "";
//
//        if (!this.getFile().isFile()) {
//            returnString += this.file.getName() + " is NOT a file.";
//        }
//        else if(this.isTestFile()) {
//            returnString += this.file.getName() + " is a test file.\nMethods:\n";
//
//            Iterator<String> iter = this.methods.iterator();
//            while (iter.hasNext()) {
//                returnString += iter.next() + "\n";
//            }
//        } else {
//            returnString += this.file.getName() + " is NOT a test file.";
//        }
//
//        return returnString;
    }
}
