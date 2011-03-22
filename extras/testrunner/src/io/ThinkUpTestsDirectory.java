package io;

import java.io.File;
import java.util.Iterator;
import java.util.TreeSet;

/**
 * This class takes a directory that may or may not contain test files,
 * it parses through all of the files, testing if they are ThinkUp unit
 * test files and ignoring the ones that are not.
 * 
 * This class relies on its state. When a directory is parsed it adds
 * test files to itself. It doesn't rely on return values. Be careful
 * when parsing numerous directories, you might not get results that
 * you are looking for if you don't use the class correctly.
 *
 * @author Sam Rose <samwho@lbak.co.uk>
 */
public class ThinkUpTestsDirectory {

    private String directory = null;
    private TreeSet<SimpleTestFile> testFiles = new TreeSet<SimpleTestFile>();

    /**
     * Initialises the class by parsing a directory.
     *
     * @param directory The directory to search for test files.
     */
    public ThinkUpTestsDirectory(String directory) {
        this.parseDirectory(directory);
    }

    /**
     * Initialises the class but does not parse anything.
     */
    public ThinkUpTestsDirectory() {
    }

    /**
     * Gets a TreeSet of the found test files.
     *
     * @return The found test files.
     */
    public TreeSet<SimpleTestFile> getTestFiles() {
        return testFiles;
    }

    /**
     * Clears the currently stores list of test files.
     */
    public void clearTestFiles() {
        testFiles.clear();
    }

    /**
     * Set and parse a given directory for test files.
     *
     * This method will add any test files it finds to the
     * currently found test files. To clear the list of test files,
     * use the clear() method of this class.
     *
     * @param directory The absolute path to the directory to scan.
     */
    public final void parseDirectory(String directory) {
        this.directory = directory.lastIndexOf('/') == directory.length() ? directory : directory + "/";
        this.parseTestFiles();
    }

    /**
     * Parse the files in the currently set directory.
     */
    private void parseTestFiles() {
        if (this.directory != null) {
            this.parseTestFiles(this.directory);
        }
    }

    /**
     * Parses files in the specified directory.
     *
     * @param directory The directory to search for test files.
     */
    private void parseTestFiles(String directory) {
        File dir = new File(directory);
        this.parseTestFiles(dir);
    }

    /**
     * This is the main file parsing method. The rest of them converge into this method
     * in some way, shape or form.
     *
     * The way this works is that it gets all of the "children" in the directory (if, indeed
     * t is a directory) and if it is a file, it parses its contents to check if it is a test
     * file. If it is a directory instead, the method calls itself with that directory, creating
     * a recursive directory search.
     *
     * @param dir
     */
    private void parseTestFiles(File dir) {
        if (dir.isDirectory()) {
            String[] children = dir.list();

            if (children != null) {
                for (int i = 0; i < children.length; i++) {
                    SimpleTestFile f = new SimpleTestFile(dir.getAbsolutePath() + "/" + children[i]);
                    if (f.isTestFile()) {
                        testFiles.add(f);
                    } else if (f.getFile().isDirectory()) {
                        this.parseTestFiles(f.getFile().getAbsolutePath());
                    }
                }
            }
        }
    }

    @Override
    public String toString() {
        if (testFiles.isEmpty()) {
            return "No test files found.";
        }

        String returnString = "Test files:\n";

        Iterator<SimpleTestFile> iter = testFiles.iterator();
        while (iter.hasNext()) {
            returnString += iter.next().getFile().getName() + "\n";
        }

        return returnString;
    }
}
